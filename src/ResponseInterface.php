<?php namespace Framework\HTTP;

/**
 * Interface ResponseInterface
 *
 * @package Framework\HTTP
 */
interface ResponseInterface
{
	/**
	 * @param int         $code
	 * @param string|null $reason
	 *
	 * @return $this
	 */
	public function setStatus(int $code, string $reason = null);

	/**
	 * @param string|null $part "code" or "reason"
	 *
	 * @return array|int|string
	 */
	public function getStatus(string $part = null);

	public function setHeader($name, string $value = null);

	public function getHeader(string $name = null);

	public function getBody(): string;

	/**
	 * @param string $body
	 *
	 * @return $this
	 */
	public function setBody(string $body);

	public function prependBody(string $content);

	public function appendBody(string $content);

	/**
	 * @return bool
	 */
	public function isSent(): bool;

	/**
	 * @return $this
	 */
	public function send();
}

