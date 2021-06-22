<?php namespace Framework\HTTP;

use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

/**
 * Class Cookie.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies
 * @see     https://tools.ietf.org/html/rfc6265
 * @see     https://php.net/manual/en/function.setcookie.php
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
		return $this->getAsString();
	}

	public function getAsString() : string
	{
		$string = $this->getName() . '=' . $this->getValue();
		$part = $this->getExpires();
		if ($part !== null) {
			$string .= '; expires=' . $this->expires->format('D, d-M-Y H:i:s') . ' GMT';
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
	public function getDomain() : ?string
	{
		return $this->domain;
	}

	/**
	 * @param string|null $domain
	 *
	 * @return $this
	 */
	public function setDomain(?string $domain)
	{
		$this->domain = $domain;
		return $this;
	}

	/**
	 * @return DateTime|null
	 */
	public function getExpires() : ?DateTime
	{
		return $this->expires;
	}

	/**
	 * @param DateTime|string|null $expires
	 *
	 * @throws Exception if can not create from format
	 *
	 * @return $this
	 */
	public function setExpires(DateTime | string | null $expires)
	{
		if ($expires instanceof DateTime) {
			$expires = clone $expires;
			$expires->setTimezone(new DateTimeZone('UTC'));
		} elseif (\is_numeric($expires)) {
			$expires = DateTime::createFromFormat('U', $expires, new DateTimeZone('UTC'));
		} elseif ($expires !== null) {
			$expires = new DateTime($expires, new DateTimeZone('UTC'));
		}
		$this->expires = $expires;
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
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	protected function setName(string $name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getPath() : ?string
	{
		return $this->path;
	}

	/**
	 * @param string|null $path
	 *
	 * @return $this
	 */
	public function setPath(?string $path)
	{
		$this->path = $path;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getSameSite() : ?string
	{
		return $this->sameSite;
	}

	/**
	 * @param string|null $sameSite Strict, Lax, Unset or None
	 *
	 * @throws InvalidArgumentException for invalid $sameSite value
	 *
	 * @return $this
	 */
	public function setSameSite(?string $sameSite)
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
	public function getValue() : string
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setValue(string $value)
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * @param bool $httpOnly
	 *
	 * @return $this
	 */
	public function setHttpOnly(bool $httpOnly = true)
	{
		$this->httpOnly = $httpOnly;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isHttpOnly() : bool
	{
		return $this->httpOnly;
	}

	/**
	 * @param bool $secure
	 *
	 * @return $this
	 */
	public function setSecure(bool $secure = true)
	{
		$this->secure = $secure;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSecure() : bool
	{
		return $this->secure;
	}

	/**
	 * @return bool
	 */
	public function send() : bool
	{
		$expires = $this->getExpires();
		if ($expires) {
			$expires = $expires->getTimestamp();
		}
		return \setcookie($this->getName(), $this->getValue(), [
			'expires' => $expires,
			'path' => $this->getPath(),
			'domain' => $this->getDomain(),
			'secure' => $this->isSecure(),
			'httponly' => $this->isHttpOnly(),
			'samesite' => $this->getSameSite(),
		]);
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
		$parts = \array_map('trim', \explode(';', $line));
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
	 * @return array|Cookie[]
	 */
	public static function create(string $line) : array
	{
		$items = \array_map('trim', \explode(';', $line));
		$cookies = [];
		foreach ($items as $item) {
			[$name, $value] = static::makeArgumentValue($item);
			if (isset($name, $value)) {
				$cookies[$name] = new Cookie($name, $value);
			}
		}
		return $cookies;
	}

	protected static function makeArgumentValue(string $part) : array
	{
		$part = \array_pad(\explode('=', $part, 2), 2, null);
		! $part[0] ?: $part[0] = \trim($part[0]);
		! $part[1] ?: $part[1] = \trim($part[1]);
		return $part;
	}
}
