<?php namespace Framework\HTTP;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use JsonException;
use LogicException;

/**
 * Class Response.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#HTTP_Responses
 */
class Response extends Message implements ResponseInterface
{
	use ResponseDownload;

	protected int $cacheSeconds = 0;
	protected bool $isSent = false;
	protected Request $request;
	/**
	 * HTTP Response Status Code.
	 */
	protected int $statusCode = 200;
	/**
	 * HTTP Response Status Reason.
	 */
	protected string $statusReason = 'OK';
	protected ?string $sendedBody = null;

	/**
	 * Response constructor.
	 *
	 * @param \Framework\HTTP\Request $request
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->setProtocol($this->request->getProtocol());
	}

	/**
	 * Get the Response body.
	 *
	 * @return string
	 */
	public function getBody() : string
	{
		if ($this->sendedBody !== null) {
			return $this->sendedBody;
		}
		$buffer = '';
		if (\ob_get_length()) {
			$buffer = \ob_get_contents();
			\ob_clean();
		}
		return $this->body .= $buffer;
	}

	/**
	 * Set the Response body.
	 *
	 * @param string $body
	 *
	 * @return \Framework\HTTP\Response
	 */
	public function setBody(string $body)
	{
		if (\ob_get_length()) {
			\ob_clean();
		}
		return parent::setBody($body);
	}

	/**
	 * Prepend a string to the body.
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function prependBody(string $content)
	{
		return parent::setBody($content . $this->getBody());
	}

	/**
	 * Append a string to the body.
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function appendBody(string $content)
	{
		return parent::setBody($this->getBody() . $content);
	}

	public function setCookie(Cookie $cookie)
	{
		return parent::setCookie($cookie);
	}

	public function setCookies(array $cookies)
	{
		return parent::setCookies($cookies);
	}

	public function removeCookie(string $name)
	{
		return parent::removeCookie($name);
	}

	public function removeCookies(array $names)
	{
		return parent::removeCookies($names);
	}

	public function setHeader(string $name, string $value)
	{
		return parent::setHeader($name, $value);
	}

	public function setHeaders(array $headers)
	{
		return parent::setHeaders($headers);
	}

	public function removeHeader(string $name)
	{
		return parent::removeHeader($name);
	}

	public function removeHeaders()
	{
		return parent::removeHeaders();
	}

	/**
	 * @return string
	 */
	public function getStatusLine() : string
	{
		return "{$this->statusCode} {$this->statusReason}";
	}

	/**
	 * Set the status line.
	 *
	 * @param int         $code
	 * @param string|null $reason
	 *
	 * @throws InvalidArgumentException if status code is invalid
	 * @throws LogicException           is status code is unknown and a reason is not set
	 *
	 * @return $this
	 */
	public function setStatusLine(int $code, string $reason = null)
	{
		$this->setStatusCode($code);
		$reason ?: $reason = static::getResponseReason($code);
		if (empty($reason)) {
			throw new LogicException("Unknown status code must have a reason: {$code}");
		}
		$this->setStatusReason($reason);
		return $this;
	}

	/**
	 * Set the status code.
	 *
	 * @param int $code
	 *
	 * @throws InvalidArgumentException if status code is invalid
	 *
	 * @return $this
	 */
	public function setStatusCode(int $code)
	{
		if ($code < 100 || $code > 599) {
			throw new InvalidArgumentException("Invalid status code: {$code}");
		}
		$this->statusCode = $code;
		return $this;
	}

	/**
	 * Get the status code.
	 *
	 * @return int
	 */
	public function getStatusCode() : int
	{
		return $this->statusCode;
	}

	/**
	 * Set a custom status reason.
	 *
	 * @param string $reason
	 *
	 * @return $this
	 */
	public function setStatusReason(string $reason)
	{
		$this->statusReason = $reason;
		return $this;
	}

	/**
	 * Get the status reason.
	 *
	 * @return string
	 */
	public function getStatusReason() : string
	{
		return $this->statusReason;
	}

	/**
	 * Say if the response was sent.
	 *
	 * @return bool
	 */
	public function isSent() : bool
	{
		return $this->isSent;
	}

	/**
	 * Sets the HTTP Redirect Response with data accessible in the next HTTP Request.
	 *
	 * @param string        $location Location Header value
	 * @param array|mixed[] $data     Session data available on next Request
	 * @param int|null      $code     HTTP Redirect status code. Leave null to determine based on
	 *                                the current HTTP method.
	 *
	 * @see  http://en.wikipedia.org/wiki/Post/Redirect/Get
	 * @see  Request::getRedirectData
	 *
	 * @throws InvalidArgumentException for invalid Redirection code
	 * @throws LogicException           if PHP Session is not active to set redirect data
	 *
	 * @return $this
	 */
	public function redirect(string $location, array $data = [], int $code = null)
	{
		if ($code === null) {
			$code = $this->request->getMethod() === 'GET' ? 307 : 303;
		} elseif ($code < 300 || $code > 308) {
			throw new InvalidArgumentException("Invalid Redirection code: {$code}");
		}
		$this->setStatusCode($code);
		$this->setHeader('Location', $location);
		if ($data) {
			if (\session_status() !== \PHP_SESSION_ACTIVE) {
				throw new LogicException('Session must be active to set redirect data');
			}
			$_SESSION['$']['redirect_data'] = $data;
		}
		return $this;
	}

	/**
	 * Send the Response headers, cookies and body to the output.
	 *
	 * @throws LogicException if Response already is sent
	 */
	public function send() : void
	{
		if ($this->isSent) {
			throw new LogicException('Response already is sent');
		}
		$this->sendHeaders();
		$this->sendCookies();
		//$this->setBody($this->getBody() . (\ob_get_length() ? \ob_get_clean() : ''));
		$this->hasDownload() ? $this->sendDownload() : $this->sendBody();
		$this->isSent = true;
	}

	protected function sendBody() : void
	{
		echo $this->sendedBody = $this->getBody();
		$this->body = '';
	}

	protected function sendCookies() : void
	{
		foreach ($this->cookies as $cookie) {
			$cookie->send();
		}
	}

	/**
	 * @throws LogicException if headers already is sent
	 */
	protected function sendHeaders() : void
	{
		if (\headers_sent()) {
			// \var_dump(\headers_list());exit;
			throw new LogicException('Headers already is sent');
		}
		// Per spec, MUST be sent with each request, if possible.
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html
		if ($this->getHeader('Date') === null) {
			$this->setDate(DateTime::createFromFormat('U', \time()));
		}
		if ($this->getHeader('Content-Type') === null) {
			$this->setContentType('text/html');
		}
		// Is good to do this?
		/*if ($this->getCacheSeconds() && empty($this->headers['ETag']))
		{
			$this->setETag(\md5($this->getBody()));
		}*/
		\header($this->getProtocol() . ' ' . $this->getStatusLine());
		parent::sendHeaders();
	}

	/**
	 * Set response body and Content-Type as JSON.
	 *
	 * @param mixed    $data
	 * @param int|null $options [optional] <p>
	 *                          Bitmask consisting of <b>JSON_HEX_QUOT</b>,
	 *                          <b>JSON_HEX_TAG</b>,
	 *                          <b>JSON_HEX_AMP</b>,
	 *                          <b>JSON_HEX_APOS</b>,
	 *                          <b>JSON_NUMERIC_CHECK</b>,
	 *                          <b>JSON_PRETTY_PRINT</b>,
	 *                          <b>JSON_UNESCAPED_SLASHES</b>,
	 *                          <b>JSON_FORCE_OBJECT</b>,
	 *                          <b>JSON_UNESCAPED_UNICODE</b>.
	 *                          <b>JSON_THROW_ON_ERROR</b>
	 *                          </p>
	 *                          <p>Default is <b>JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE</b>
	 *                          when null</p>
	 * @param int      $depth   [optional] <p>
	 *                          Set the maximum depth. Must be greater than zero.
	 *                          </p>
	 *
	 * @throws JsonException if json_encode() fails
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

	/**
	 * Set the Cache-Control header.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
	 * @see https://stackoverflow.com/a/3492459/6027968
	 *
	 * @param int  $seconds
	 * @param bool $public
	 *
	 * @return $this
	 */
	public function setCache(int $seconds, bool $public = false)
	{
		$date = new DateTime();
		$date->modify('+' . $seconds . ' seconds');
		$this->setExpires($date);
		$this->setHeader(
			'Cache-Control',
			($public ? 'public' : 'private') . ', max-age=' . $seconds
		);
		$this->cacheSeconds = $seconds;
		return $this;
	}

	/**
	 * Clear the browser cache.
	 *
	 * @return $this
	 */
	public function setNoCache()
	{
		$this->setHeader('Cache-Control', 'no-cache, no-store, max-age=0');
		$this->cacheSeconds = 0;
		return $this;
	}

	/**
	 * Get the number of seconds the cache is active.
	 *
	 * @return int
	 */
	public function getCacheSeconds() : int
	{
		return $this->cacheSeconds;
	}

	/**
	 * Set the Content-Type header.
	 *
	 * @param string $mime
	 * @param string $charset
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
	 *
	 * @return $this
	 */
	public function setContentType(string $mime, string $charset = 'UTF-8')
	{
		$this->setHeader('Content-Type', $mime . ($charset ? '; charset=' . $charset : ''));
		return $this;
	}

	/**
	 * Set the Content-Language header.
	 *
	 * @param string $language
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Language
	 *
	 * @return $this
	 */
	public function setContentLanguage(string $language)
	{
		$this->setHeader('Content-Language', $language);
		return $this;
	}

	/**
	 * Set the Content-Encoding header.
	 *
	 * @param string $encoding
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Encoding
	 *
	 * @return $this
	 */
	public function setContentEncoding(string $encoding)
	{
		$this->setHeader('Content-Encoding', $encoding);
		return $this;
	}

	/**
	 * Set the Date header.
	 *
	 * @param DateTime $datetime
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Date
	 *
	 * @return $this
	 */
	public function setDate(DateTime $datetime)
	{
		$date = clone $datetime;
		$date->setTimezone(new DateTimeZone('UTC'));
		$this->setHeader('Date', $date->format(DateTime::RFC7231));
		return $this;
	}

	/**
	 * Set the ETag header.
	 *
	 * @param string $etag
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag
	 *
	 * @return $this
	 */
	public function setETag(string $etag)
	{
		$this->setHeader('ETag', $etag);
		return $this;
	}

	/**
	 * Se the Expires header.
	 *
	 * @param DateTime $datetime
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expires
	 *
	 * @return $this
	 */
	public function setExpires(DateTime $datetime)
	{
		$date = clone $datetime;
		$date->setTimezone(new DateTimeZone('UTC'));
		$this->setHeader('Expires', $date->format(DateTime::RFC7231));
		return $this;
	}

	/**
	 * Set the Last-Modified header.
	 *
	 * @param DateTime $datetime
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified
	 *
	 * @return $this
	 */
	public function setLastModified(DateTime $datetime)
	{
		$date = clone $datetime;
		$date->setTimezone(new DateTimeZone('UTC'));
		$this->setHeader('Last-Modified', $date->format(DateTime::RFC7231));
		return $this;
	}

	/**
	 * Set the status line as "Not Modified".
	 *
	 * @return $this
	 */
	public function setNotModified()
	{
		$this->setStatusLine(304, 'Not Modified');
		return $this;
	}
}
