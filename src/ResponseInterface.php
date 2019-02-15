<?php namespace Framework\HTTP;

/**
 * Interface ResponseInterface.
 */
interface ResponseInterface
{
	public function appendBody(string $content);

	public function getBody() : string;

	public function getHeader(string $name = null);

	/**
	 * @param string|null $part "code" or "reason"
	 *
	 * @return array|int|string
	 */
	public function getStatus(string $part = null);

	/**
	 * @return bool
	 */
	public function isSent() : bool;

	public function prependBody(string $content);

	/**
	 * @return $this
	 */
	public function send();

	/**
	 * @param string $body
	 *
	 * @return $this
	 */
	public function setBody(string $body);

	public function setHeader($name, string $value = null);

	/**
	 * @param int         $code
	 * @param string|null $reason
	 *
	 * @return $this
	 */
	public function setStatus(int $code, string $reason = null);
}
