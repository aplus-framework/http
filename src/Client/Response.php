<?php namespace Framework\HTTP\Client;

use Framework\HTTP\Message;

class Response extends Message
{
	protected $statusCode;

	public function __construct(int $status, array $headers, string $body = null)
	{
		$this->statusCode = $status;
		$this->setHeaders($headers);
		$this->setBody($body);
	}

	public function setBody(?string $body)
	{
		$this->body = $body;
		return $this;
	}

	public function getStatusCode() : int
	{
		return $this->statusCode;
	}

	public function getBody() : ?string
	{
		return $this->body;
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

	public function getCookie(string $name)
	{
		// TODO: Implement getCookie() method.
	}

	/**
	 * @return array
	 */
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

	protected function setHeaders(array $headers)
	{
		foreach ($headers as $name => $value) {
			$this->headers[$this->getHeaderName($name)] = $value;
		}
		return $this;
	}

	public function getProtocol()
	{
		// TODO: Implement getProtocol() method.
	}
}
