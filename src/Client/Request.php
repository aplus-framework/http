<?php namespace Framework\HTTP\Client;

use Framework\HTTP\Message;
use Framework\HTTP\URL;

class Request extends Message
{
	protected $protocol = 1.1;
	protected $method = 'GET';
	/**
	 * @var URL
	 */
	protected $url;

	/**
	 * Request constructor.
	 *
	 * @param string|URL $url
	 */
	public function __construct($url)
	{
		$this->setURL($url);
	}

	public function setURL($url)
	{
		if ( ! $url instanceof URL) {
			$url = new URL($url);
		}
		$this->url = $url;
		return $this;
	}

	public function getURL() : URL
	{
		return $this->url;
	}

	public function setMethod(string $method) : void
	{
		$method = \strtoupper($method);
		if ( ! \in_array($method, [
			'GET',
			'HEAD',
			'POST',
			'PATCH',
			'PUT',
			'DELETE',
			'OPTIONS',
		], true)) {
			throw new \InvalidArgumentException("Invalid HTTP method: {$method}");
		}
		$this->method = $method;
	}

	public function getMethod() : string
	{
		return $this->method;
	}

	public function getProtocol()
	{
		return $this->protocol;
	}

	public function setProtocol(float $protocol)
	{
		$this->protocol = $protocol;
		return $this;
	}

	public function getBody() : ?string
	{
		return $this->body;
	}

	public function setBody($body)
	{
		if ( ! \is_scalar($body)) {
			$body = \http_build_query($body);
		}
		$this->body = $body;
		return $this;
	}

	/**
	 * @param mixed $data
	 * @param int   $options [optional] <p>
	 *                       Bitmask consisting of <b>JSON_HEX_QUOT</b>,
	 *                       <b>JSON_HEX_TAG</b>,
	 *                       <b>JSON_HEX_AMP</b>,
	 *                       <b>JSON_HEX_APOS</b>,
	 *                       <b>JSON_NUMERIC_CHECK</b>,
	 *                       <b>JSON_PRETTY_PRINT</b>,
	 *                       <b>JSON_UNESCAPED_SLASHES</b>,
	 *                       <b>JSON_FORCE_OBJECT</b>,
	 *                       <b>JSON_UNESCAPED_UNICODE</b>.
	 *                       <b>JSON_THROW_ON_ERROR</b>
	 *                       </p>
	 *                       <p>Default is <b>JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE</b>
	 *                       when null</p>
	 * @param int   $depth   [optional] <p>
	 *                       Set the maximum depth. Must be greater than zero.
	 *                       </p>
	 *
	 * @throws \JsonException if json_encode() fails
	 *
	 * @return $this
	 */
	public function setJSON($data, int $options = null, int $depth = 512)
	{
		if ($options === null) {
			$options = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;
		}
		$data = \json_encode($data, $options | \JSON_THROW_ON_ERROR, $depth);
		$this->setContentType('application/json');
		$this->setBody($data);
		return $this;
	}

	public function setContentType(string $mime, string $charset = 'UTF-8')
	{
		$this->setHeader('Content-Type', $mime . ($charset ? '; charset=' . $charset : ''));
		return $this;
	}

	public function getCookie(string $name) : ?string
	{
		return $this->cookies[$name] ?? null;
	}

	public function setCookie(string $name, string $value)
	{
		$this->cookies[$name] = $value;
		$this->setCookieHeader();
		return $this;
	}

	public function removeCookie(string $name)
	{
		unset($this->cookies[$name]);
		$this->setCookieHeader();
		return $this;
	}

	protected function setCookieHeader()
	{
		$cookie = null;
		foreach ($this->getCookies() as $name => $value) {
			$cookie .= $name . '=' . $value . '; ';
		}
		if ($cookie) {
			$cookie = \rtrim($cookie, '; ');
			return $this->setHeader('Cookie', $cookie);
		}
		return $this->removeHeader('Cookie');
	}

	public function setCookies(array $cookies)
	{
		foreach ($cookies as $name => $value) {
			$this->setCookie($name, $value);
		}
		return $this;
	}

	public function getCookies() : array
	{
		return $this->cookies;
	}

	public function getHeader(string $name) : ?string
	{
		return $this->headers[$this->getHeaderName($name)] ?? null;
	}

	public function getHeaders() : array
	{
		return $this->headers;
	}

	public function setHeader(string $name, string $value)
	{
		$this->headers[$this->getHeaderName($name)] = $value;
		return $this;
	}

	public function setHeaders(array $headers)
	{
		foreach ($headers as $name => $value) {
			$this->setHeader($name, $value);
		}
		return $this;
	}

	public function removeHeader(string $name)
	{
		unset($this->headers[$this->getHeaderName($name)]);
		return $this;
	}
}
