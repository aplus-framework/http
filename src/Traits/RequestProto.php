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
 * Trait RequestProto.
 *
 * @package http
 */
trait RequestProto
{
    protected Closure | string $protoKey = 'HTTPS';
    protected string $proto;

    /**
     * Get the proto.
     *
     * @return string
     */
    public function getProto() : string
    {
        return $this->proto;
    }

    /**
     * @throws RuntimeException for invalid host
     *
     * @return static
     */
    protected function setProto() : static
    {
        $key = $this->getProtoKey();
        if ($key instanceof Closure) {
            $proto = $key();
            $this->validateProto($proto, Closure::class);
            $this->proto = $proto;
            return $this;
        }
        $proto = match($key) {
            'HTTP_FORWARDED' => $this->getProtoFromHttpForwarded(),
            'HTTP_X_FORWARDED_PROTO' => $this->getProtoFromHttpXForwardedProto(),
            'HTTPS' => $this->getProtoFromHttps(),
            'REQUEST_SCHEME' => $this->getProtoFromRequestScheme(),
            default => $_SERVER[$key]
        };
        $this->validateProto($proto, $key);
        $this->proto = $proto;
        return $this;
    }

    protected function validateProto(string $proto, string $key) : void
    {
        if (!\in_array($proto, ['http', 'https'], true)) {
            throw new RuntimeException(
                "The value of {$key} is not a valid proto"
            );
        }
    }

    /**
     * Get proto from the X-Forwarded-Proto header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Forwarded-Proto
     *
     * @return string The proto
     */
    protected function getProtoFromHttpXForwardedProto() : string
    {
        $ip = \explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'], 2)[0];
        $ip = \trim($ip);
        return $ip;
    }

    /**
     * Get proto from the Forwarded header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Forwarded
     *
     * @return string The proto
     */
    protected function getProtoFromHttpForwarded() : string
    {
        $directives = \explode(',', $_SERVER['HTTP_FORWARDED'], 2)[0];
        $directives = \explode(';', $directives);
        foreach ($directives as $directive) {
            $directive = \trim($directive);
            if (!\str_starts_with(\strtolower($directive), 'proto')) {
                continue;
            }
            $directive = \explode('=', $directive, 2)[1];
            $directive = \trim($directive);
            return $directive;
        }
        throw new RuntimeException(
            'The proto could not be get from the Forwarded header'
        );
    }

    protected function getProtoFromHttps() : string
    {
        if (isset($_SERVER['HTTPS']) && \strtolower($_SERVER['HTTPS']) === 'on') {
            return 'https';
        }
        return 'http';
    }

    protected function getProtoFromRequestScheme() : string
    {
        return $_SERVER['REQUEST_SCHEME'];
    }

    /**
     * Set the key used to get the proto from the super-global $_SERVER or a
     * Closure.
     *
     * @param Closure|string $protoKey
     *
     * @throws InvalidArgumentException for proto key not set in the $_SERVER var
     *
     * @return static
     */
    protected function setProtoKey(Closure | string $protoKey) : static
    {
        if (\is_string($protoKey) && !isset($_SERVER[$protoKey])) {
            throw new InvalidArgumentException(
                'The Proto Key "' . $protoKey . '" is not set in the $_SERVER'
            );
        }
        $this->protoKey = $protoKey;
        return $this;
    }

    /**
     * Get the key used to get the proto from the super-global $_SERVER or
     * Closure.
     *
     * @return Closure|string
     */
    public function getProtoKey() : Closure | string
    {
        return $this->protoKey;
    }

    /**
     * Say if the connection has HTTPS.
     *
     * @return bool
     */
    public function isSecure() : bool
    {
        return $this->getProto() === 'https';
    }
}
