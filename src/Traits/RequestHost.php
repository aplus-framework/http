<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Aplus\Http\Traits;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

/**
 * Trait RequestHost.
 *
 * @package http
 */
trait RequestHost
{
    protected Closure | string $hostKey = 'HTTP_HOST';
    protected string $host;
    /**
     * @var array<string>
     */
    protected array $allowedHosts = [];

    /**
     * Get the host.
     *
     * @return string
     */
    public function getHost() : string
    {
        return $this->host;
    }

    /**
     * @throws RuntimeException for invalid host
     *
     * @return static
     */
    protected function setHost() : static
    {
        $key = $this->getHostKey();
        if ($key instanceof Closure) {
            $host = $key();
            $this->host = $host;
            return $this;
        }
        $host = match($key) {
            'HTTP_FORWARDED' => $this->getHostFromHttpForwarded(),
            'HTTP_X_FORWARDED_HOST' => $this->getHostFromHttpXForwardedHost(),
            default => $_SERVER[$key]
        };
        $this->host = $host;
        return $this;
    }

    /**
     * Get host from the X-Forwarded-Host header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Forwarded-Host
     *
     * @return string The host
     */
    protected function getHostFromHttpXForwardedHost() : string
    {
        return $_SERVER['HTTP_X_FORWARDED_HOST'];
    }

    /**
     * Get host from the Forwarded header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Forwarded
     *
     * @return string The host
     */
    protected function getHostFromHttpForwarded() : string
    {
        $directives = \explode(',', $_SERVER['HTTP_FORWARDED'], 2)[0];
        $directives = \explode(';', $directives);
        foreach ($directives as $directive) {
            $directive = \trim($directive);
            if (!\str_starts_with(\strtolower($directive), 'host')) {
                continue;
            }
            $directive = \explode('=', $directive, 2)[1];
            $directive = \trim($directive);
            return $directive;
        }
        throw new RuntimeException(
            'The host could not be get from the Forwarded header'
        );
    }

    /**
     * Set the key used to get the host from the super-global $_SERVER or a
     * Closure.
     *
     * @param Closure|string $hostKey
     *
     * @throws InvalidArgumentException for host key not set in the $_SERVER var
     *
     * @return static
     */
    protected function setHostKey(Closure | string $hostKey) : static
    {
        if (\is_string($hostKey) && !isset($_SERVER[$hostKey])) {
            throw new InvalidArgumentException(
                'The Host Key "' . $hostKey . '" is not set in the $_SERVER'
            );
        }
        $this->hostKey = $hostKey;
        return $this;
    }

    /**
     * Get the key used to get the host from the super-global $_SERVER or
     * Closure.
     *
     * @return Closure|string
     */
    public function getHostKey() : Closure | string
    {
        return $this->hostKey;
    }

    /**
     * Check if Host header is allowed. And set allowed hosts.
     *
     * @see https://expressionengine.com/blog/http-host-and-server-name-security-issues
     * @see http://nginx.org/en/docs/http/request_processing.html
     *
     * @param array<string> $allowedHosts
     *
     * @return static
     */
    protected function setAllowedHosts(array $allowedHosts) : static
    {
        if (empty($allowedHosts)) {
            $this->allowedHosts = $allowedHosts;
            return $this;
        }
        if (!\in_array($this->getHost(), $allowedHosts, true)) {
            throw new UnexpectedValueException('Invalid Host: ' . $this->getHost());
        }
        $this->allowedHosts = $allowedHosts;
        return $this;
    }

    /**
     * Get the allowed hosts.
     *
     * @return array<string>
     */
    public function getAllowedHosts() : array
    {
        return $this->allowedHosts;
    }
}
