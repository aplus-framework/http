<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;

/**
 * Class Cookie.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies
 * @see https://datatracker.ietf.org/doc/html/rfc6265
 * @see https://www.php.net/manual/en/function.setcookie.php
 *
 * @package http
 */
class Cookie implements \Stringable
{
    protected ?string $domain = null;
    protected ?DateTime $expires = null;
    protected bool $httpOnly = false;
    protected string $name;
    protected ?string $path = null;
    protected ?string $sameSite = null;
    protected bool $secure = false;
    protected string $value;

    /**
     * Cookie constructor.
     *
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value)
    {
        $this->setName($name);
        $this->setValue($value);
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    /**
     * @return string
     *
     * @deprecated Use {@see Cookie::toString()}
     *
     * @codeCoverageIgnore
     */
    #[Deprecated(
        reason: 'since HTTP Library version 5.3, use toString() instead',
        replacement: '%class%->toString()'
    )]
    public function getAsString() : string
    {
        \trigger_error(
            'Method ' . __METHOD__ . ' is deprecated',
            \E_USER_DEPRECATED
        );
        return $this->toString();
    }

    /**
     * @since 5.3
     *
     * @return string
     */
    public function toString() : string
    {
        $string = $this->getName() . '=' . $this->getValue();
        $part = $this->getExpires();
        if ($part !== null) {
            $string .= '; expires=' . $this->expires->format(DateTimeInterface::RFC7231);
            $string .= '; Max-Age=' . $this->expires->diff(new DateTime('-1 second'))->s;
        }
        $part = $this->getPath();
        if ($part !== null) {
            $string .= '; path=' . $part;
        }
        $part = $this->getDomain();
        if ($part !== null) {
            $string .= '; domain=' . $part;
        }
        $part = $this->isSecure();
        if ($part) {
            $string .= '; secure';
        }
        $part = $this->isHttpOnly();
        if ($part) {
            $string .= '; HttpOnly';
        }
        $part = $this->getSameSite();
        if ($part !== null) {
            $string .= '; SameSite=' . $part;
        }
        return $string;
    }

    /**
     * @return string|null
     */
    #[Pure]
    public function getDomain() : ?string
    {
        return $this->domain;
    }

    /**
     * @param string|null $domain
     *
     * @return static
     */
    public function setDomain(?string $domain) : static
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    #[Pure]
    public function getExpires() : ?DateTime
    {
        return $this->expires;
    }

    /**
     * @param DateTime|int|string|null $expires
     *
     * @throws Exception if can not create from format
     *
     * @return static
     */
    public function setExpires(DateTime | int | string | null $expires) : static
    {
        if ($expires instanceof DateTime) {
            $expires = clone $expires;
            $expires->setTimezone(new DateTimeZone('UTC'));
        } elseif (\is_numeric($expires)) {
            $expires = DateTime::createFromFormat('U', (string) $expires, new DateTimeZone('UTC'));
        } elseif ($expires !== null) {
            $expires = new DateTime($expires, new DateTimeZone('UTC'));
        }
        $this->expires = $expires; // @phpstan-ignore-line
        return $this;
    }

    /**
     * @return bool
     */
    public function isExpired() : bool
    {
        return $this->getExpires() && \time() > $this->getExpires()->getTimestamp();
    }

    /**
     * @return string
     */
    #[Pure]
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    protected function setName(string $name) : static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    #[Pure]
    public function getPath() : ?string
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     *
     * @return static
     */
    public function setPath(?string $path) : static
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string|null
     */
    #[Pure]
    public function getSameSite() : ?string
    {
        return $this->sameSite;
    }

    /**
     * @param string|null $sameSite Strict, Lax, Unset or None
     *
     * @throws InvalidArgumentException for invalid $sameSite value
     *
     * @return static
     */
    public function setSameSite(?string $sameSite) : static
    {
        if ($sameSite !== null) {
            $sameSite = \ucfirst(\strtolower($sameSite));
            if ( ! \in_array($sameSite, ['Strict', 'Lax', 'Unset', 'None'])) {
                throw new InvalidArgumentException('SameSite must be Strict, Lax, Unset or None');
            }
        }
        $this->sameSite = $sameSite;
        return $this;
    }

    /**
     * @return string
     */
    #[Pure]
    public function getValue() : string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return static
     */
    public function setValue(string $value) : static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param bool $httpOnly
     *
     * @return static
     */
    public function setHttpOnly(bool $httpOnly = true) : static
    {
        $this->httpOnly = $httpOnly;
        return $this;
    }

    /**
     * @return bool
     */
    #[Pure]
    public function isHttpOnly() : bool
    {
        return $this->httpOnly;
    }

    /**
     * @param bool $secure
     *
     * @return static
     */
    public function setSecure(bool $secure = true) : static
    {
        $this->secure = $secure;
        return $this;
    }

    /**
     * @return bool
     */
    #[Pure]
    public function isSecure() : bool
    {
        return $this->secure;
    }

    /**
     * @return bool
     */
    public function send() : bool
    {
        $options = [];
        $value = $this->getExpires();
        if ($value) {
            $options['expires'] = $value->getTimestamp();
        }
        $value = $this->getPath();
        if ($value !== null) {
            $options['path'] = $value;
        }
        $value = $this->getDomain();
        if ($value !== null) {
            $options['domain'] = $value;
        }
        $options['secure'] = $this->isSecure();
        $options['httponly'] = $this->isHttpOnly();
        $value = $this->getSameSite();
        if ($value !== null) {
            $options['samesite'] = $value;
        }
        // @phpstan-ignore-next-line
        return \setcookie($this->getName(), $this->getValue(), $options);
    }

    /**
     * Parses a Set-Cookie Header line and creates a new Cookie object.
     *
     * @param string $line
     *
     * @throws Exception if setExpires fail
     *
     * @return Cookie|null
     */
    public static function parse(string $line) : ?Cookie
    {
        $parts = \array_map('\trim', \explode(';', $line, 20));
        $cookie = null;
        foreach ($parts as $key => $part) {
            [$arg, $val] = static::makeArgumentValue($part);
            if ($key === 0) {
                if (isset($arg, $val)) {
                    $cookie = new Cookie($arg, $val);
                    continue;
                }
                break;
            }
            if ($arg === null) {
                continue;
            }
            switch (\strtolower($arg)) {
                case 'expires':
                    $cookie->setExpires($val);
                    break;
                case 'domain':
                    $cookie->setDomain($val);
                    break;
                case 'path':
                    $cookie->setPath($val);
                    break;
                case 'httponly':
                    $cookie->setHttpOnly();
                    break;
                case 'secure':
                    $cookie->setSecure();
                    break;
                case 'samesite':
                    $cookie->setSameSite($val);
                    break;
            }
        }
        return $cookie;
    }

    /**
     * Create Cookie objects from a Cookie Header line.
     *
     * @param string $line
     *
     * @return array<string,Cookie>
     */
    public static function create(string $line) : array
    {
        $items = \array_map('\trim', \explode(';', $line, 3000));
        $cookies = [];
        foreach ($items as $item) {
            [$name, $value] = static::makeArgumentValue($item);
            if (isset($name, $value)) {
                $cookies[$name] = new Cookie($name, $value);
            }
        }
        return $cookies;
    }

    /**
     * @param string $part
     *
     * @return array<int,string|null>
     */
    protected static function makeArgumentValue(string $part) : array
    {
        $part = \array_pad(\explode('=', $part, 2), 2, null);
        if ($part[0] !== null) {
            $part[0] = static::trimmedOrNull($part[0]);
        }
        if ($part[1] !== null) {
            $part[1] = static::trimmedOrNull($part[1]);
        }
        return $part;
    }

    protected static function trimmedOrNull(string $value) : ?string
    {
        $value = \trim($value);
        if ($value === '') {
            $value = null;
        }
        return $value;
    }
}
