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

/**
 * Trait RequestIpTrait.
 *
 * @package http
 */
trait RequestIpTrait
{
    protected Closure | string $ipKey = 'REMOTE_ADDR';
    protected string $ip;

    /**
     * Get the connection IP.
     *
     * @return string
     */
    public function getIp() : string
    {
        return $this->ip;
    }

    /**
     * @throws RuntimeException for invalid IP address
     *
     * @return static
     */
    protected function setIp() : static
    {
        $key = $this->getIpKey();
        if ($key instanceof Closure) {
            $ip = $key();
            $this->validateIp($ip, Closure::class);
            $this->ip = $ip;
            return $this;
        }
        $ip = match($key) {
            'HTTP_FORWARDED' => $this->getIpFromHttpForwarded(),
            'HTTP_X_FORWARDED_FOR' => $this->getIpFromHttpXForwardedFor(),
            default => $_SERVER[$key]
        };
        $this->validateIp($ip, $key);
        $this->ip = $ip;
        return $this;
    }

    protected function validateIp(string $ip, string $key) : void
    {
        if (!\filter_var($ip, \FILTER_VALIDATE_IP)) {
            throw new RuntimeException(
                "The value of {$key} is not a valid IP address"
            );
        }
    }

    /**
     * Get IP from the X-Forwarded-For header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Forwarded-For
     *
     * @return string The IP address
     */
    protected function getIpFromHttpXForwardedFor() : string
    {
        $ip = \explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'], 2)[0];
        $ip = \trim($ip);
        return $ip;
    }

    /**
     * Get IP from the Forwarded header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Forwarded
     *
     * @return string The IP address
     */
    protected function getIpFromHttpForwarded() : string
    {
        $directives = \explode(',', $_SERVER['HTTP_FORWARDED'], 2)[0];
        $directives = \explode(';', $directives);
        foreach ($directives as $directive) {
            $directive = \trim($directive);
            if (!\str_starts_with(\strtolower($directive), 'for')) {
                continue;
            }
            $directive = \explode('=', $directive, 2)[1];
            $directive = \trim($directive);
            if (\str_starts_with($directive, '"')) {
                $directive = \substr($directive, 1, -1);
            }
            if (\str_starts_with($directive, '[')) {
                $pos = \strrpos($directive, ']');
                $directive = \substr($directive, 1, $pos - 1);
            }
            return $directive;
        }
        throw new RuntimeException(
            'The IP address could not be get from the Forwarded header'
        );
    }

    /**
     * Set the key used to get the IP address from the super-global $_SERVER or a
     * Closure.
     *
     * @param Closure|string $ipKey
     *
     * @throws InvalidArgumentException for IP key not set in the $_SERVER var
     *
     * @return static
     */
    protected function setIpKey(Closure | string $ipKey) : static
    {
        if (\is_string($ipKey) && !isset($_SERVER[$ipKey])) {
            throw new InvalidArgumentException(
                'The IP Key "' . $ipKey . '" is not set in the $_SERVER'
            );
        }
        $this->ipKey = $ipKey;
        return $this;
    }

    /**
     * Get the key used to get the IP address from the super-global $_SERVER or
     * Closure.
     *
     * @return Closure|string
     */
    public function getIpKey() : Closure | string
    {
        return $this->ipKey;
    }
}
