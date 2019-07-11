<?php namespace Framework\HTTP\Client;

use Framework\HTTP\Cookie;
use Framework\HTTP\Message;
use Framework\HTTP\ResponseInterface;

class Response extends Message implements ResponseInterface
{
	/**
	 * @var string
	 */
	protected $protocol;
	/**
	 * @var int
	 */
	protected $statusCode;
	/**
	 * @var string
	 */
	protected $statusReason;

	public function __construct(
		string $protocol,
		int $status,
		string $reason,
		array $headers,
		string $body
	) {
		$this->setProtocol($protocol);
		$this->setStatusCode($status);
		$this->setStatusReason($reason);
		$this->setHeaders($headers);
		$this->setBody($body);
	}

	public function getStatusCode() : int
	{
		return $this->statusCode;
	}

	protected function setStatusCode(int $statusCode)
	{
		$this->statusCode = $statusCode;
		return $this;
	}

	public function getStatusReason() : string
	{
		return $this->statusReason;
	}

	protected function setStatusReason(string $statusReason)
	{
		$this->statusReason = $statusReason;
		return $this;
	}

	protected function setHeader(string $name, string $value)
	{
		if (\strtolower($name) === 'set-cookie') {
			$cookie = $this->parseCookieLine($value);
			if ($cookie) {
				$this->setCookie($cookie);
			}
		}
		return parent::setHeader($name, $value);
	}

	protected function parseCookieLine(string $line) : ?Cookie
	{
		$parts = \explode(';', $line);
		$parts = \array_map('trim', $parts);
		$cookie = null;
		foreach ($parts as $key => $part) {
			[$arg, $val] = \array_pad(\explode('=', $part, 2), 2, null);
			if ($key === 0 && isset($arg, $val)) {
				$cookie = new Cookie($arg, $val);
				continue;
			}
			if ($cookie === null && $key > 0) {
				break;
			}
			$arg = \strtolower($arg);
			if ($arg === 'expires') {
				$cookie->setExpires($val);
				continue;
			}
			if ($arg === 'domain') {
				$cookie->setDomain($val);
				continue;
			}
			if ($arg === 'path') {
				$cookie->setPath($val);
				continue;
			}
			if ($arg === 'httponly') {
				$cookie->setHttpOnly();
				continue;
			}
			if ($arg === 'secure') {
				$cookie->setSecure();
				continue;
			}
			if ($arg === 'samesite') {
				$cookie->setSameSite($val);
				continue;
			}
		}
		return $cookie;
	}

	/**
	 * @param bool $assoc
	 * @param int  $options
	 * @param int  $depth
	 *
	 * @return array|false|object
	 */
	public function getJSON(bool $assoc = false, int $options = null, int $depth = 512)
	{
		if ($options === null) {
			$options = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES;
		}
		$body = \json_decode($this->getBody(), $assoc, $depth, $options);
		if (\json_last_error() !== \JSON_ERROR_NONE) {
			return false;
		}
		return $body;
	}
}
