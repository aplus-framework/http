<?php namespace Framework\HTTP;

/**
 * Class Cookie
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies
 * @see     https://tools.ietf.org/html/rfc6265
 * @see     https://php.net/manual/en/function.setcookie.php
 *
 * @package Framework\HTTP
 */
class Cookie
{
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @var string
	 */
	protected $value;
	/**
	 * @var \DateTime|null
	 */
	protected $expires;
	/**
	 * @var string|null
	 */
	protected $path;
	/**
	 * @var string|null
	 */
	protected $domain;
	/**
	 * @var bool
	 */
	protected $secure = false;
	/**
	 * @var bool
	 */
	protected $httpOnly = false;
	/**
	 * @var string|null
	 */
	protected $sameSite;

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

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setName(string $name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getValue(): string
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
	 * @return \DateTime|null
	 */
	public function getExpires(): ?\DateTime
	{
		return $this->expires;
	}

	/**
	 * @param \DateTime|string|null $expires
	 *
	 * @return $this
	 */
	public function setExpires($expires)
	{
		if ($expires instanceof \DateTime)
		{
			$expires = clone $expires;
			$expires->setTimezone(new \DateTimeZone('UTC'));
		}
		elseif (\is_numeric($expires))
		{
			$expires = \DateTime::createFromFormat('U', $expires, new \DateTimeZone('UTC'));
		}
		elseif ($expires !== null)
		{
			$expires = new \DateTime($expires, new \DateTimeZone('UTC'));
		}

		$this->expires = $expires;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getPath(): ?string
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
	public function getDomain(): ?string
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
	 * @return bool
	 */
	public function isSecure(): bool
	{
		return $this->secure;
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
	public function isHttpOnly(): bool
	{
		return $this->httpOnly;
	}

	/**
	 * @param bool $http_only
	 *
	 * @return $this
	 */
	public function setHttpOnly(bool $http_only = true)
	{
		$this->httpOnly = $http_only;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getSameSite(): ?string
	{
		return $this->sameSite;
	}

	/**
	 * @param string|null $same_site Strict, Lax or Unset
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException for invalid $same_site value
	 */
	public function setSameSite(?string $same_site)
	{
		if ($same_site !== null)
		{
			$same_site = \ucfirst(\strtolower($same_site));

			if ( ! \in_array($same_site, ['Strict', 'Lax', 'Unset']))
			{
				throw new \InvalidArgumentException('SameSite must be Strict, Lax or Unset');
			}
		}

		$this->sameSite = $same_site;

		return $this;
	}

	public function getAsString(): string
	{
		$string = $this->getName() . '=' . $this->getValue();

		$part = $this->getExpires();

		if ($part !== null)
		{
			$string .= '; expires=' . $this->expires->format('D, d-M-Y H:i:s') . ' GMT';
			$string .= '; Max-Age=' . $this->expires->diff(new \DateTime('-1 second'))->s;
		}

		$part = $this->getPath();

		if ($part !== null)
		{
			$string .= '; path=' . $part;
		}

		$part = $this->getDomain();

		if ($part !== null)
		{
			$string .= '; domain=' . $part;
		}

		$part = $this->isSecure();

		if ($part)
		{
			$string .= '; secure';
		}

		$part = $this->isHttpOnly();

		if ($part)
		{
			$string .= '; HttpOnly';
		}

		$part = $this->getSameSite();

		if ($part !== null)
		{
			$string .= '; SameSite=' . $part;
		}

		return $string;
	}

	/**
	 * @return bool
	 */
	public function send(): bool
	{
		$expires = $this->getExpires();

		if ($expires)
		{
			$expires = (int)$expires->format('U');
		}

		return \setcookie($this->getName(), $this->getValue(), [
			'expires'  => $expires,
			'path'     => $this->getPath(),
			'domain'   => $this->getDomain(),
			'secure'   => $this->isSecure(),
			'httponly' => $this->isHttpOnly(),
			'samesite' => $this->getSameSite(),
		]);
	}

	public function __toString(): string
	{
		return $this->getAsString();
	}
}
