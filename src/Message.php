<?php namespace Framework\HTTP;

/**
 * Class Message.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages
 * @see     https://tools.ietf.org/html/rfc7231
 */
abstract class Message
{
	/**
	 * HTTP Message Protocol.
	 */
	protected string $protocol = 'HTTP/1.1';
	/**
	 * HTTP Request URL.
	 */
	protected URL $url;
	/**
	 * HTTP Request Method.
	 */
	protected string $method;
	/**
	 * HTTP Message Body.
	 */
	protected string $body = '';
	/**
	 * HTTP Message Cookies.
	 *
	 * @var array|Cookie[]
	 */
	protected array $cookies = [];
	/**
	 * HTTP Message Headers.
	 *
	 * @var array|array[]
	 */
	protected array $headers = [];
	/**
	 * Standard Headers.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
	 *
	 * @var array|string[]
	 */
	protected static array $standardHeaders = [
		// General
		'cache-control' => 'Cache-Control',
		'connection' => 'Connection',
		'content-disposition' => 'Content-Disposition',
		'date' => 'Date',
		'keep-alive' => 'Keep-Alive',
		'via' => 'Via',
		'warning' => 'Warning',
		// Entity
		'allow' => 'Allow',
		'content-encoding' => 'Content-Encoding',
		'content-language' => 'Content-Language',
		'content-length' => 'Content-Length',
		'content-location' => 'Content-Location',
		'content-type' => 'Content-Type',
		// Request
		'accept' => 'Accept',
		'accept-charset' => 'Accept-Charset',
		'accept-encoding' => 'Accept-Encoding',
		'accept-language' => 'Accept-Language',
		'access-control-request-headers' => 'Access-Control-Request-Headers',
		'access-control-request-method' => 'Access-Control-Request-Method',
		'authorization' => 'Authorization',
		'cookie' => 'Cookie',
		'dnt' => 'DNT',
		'expect' => 'Expect',
		'forwarded' => 'Forwarded',
		'from' => 'From',
		'host' => 'Host',
		'if-match' => 'If-Match',
		'if-modified-since' => 'If-Modified-Since',
		'if-none-match' => 'If-None-Match',
		'if-range' => 'If-Range',
		'if-unmodified-since' => 'If-Unmodified-Since',
		'origin' => 'Origin',
		'proxy-authorization' => 'Proxy-Authorization',
		'range' => 'Range',
		'referer' => 'Referer',
		'te' => 'TE',
		'upgrade-insecure-requests' => 'Upgrade-Insecure-Requests',
		'user-agent' => 'User-Agent',
		'x-forwarded-for' => 'X-Forwarded-For',
		'x-forwarded-host' => 'X-Forwarded-Host',
		'x-forwarded-proto' => 'X-Forwarded-Proto',
		'x-requested-with' => 'X-Requested-With',
		// Response
		'accept-ranges' => 'Accept-Ranges',
		'access-control-allow-credentials' => 'Access-Control-Allow-Credentials',
		'access-control-allow-headers' => 'Access-Control-Allow-Headers',
		'access-control-allow-methods' => 'Access-Control-Allow-Methods',
		'access-control-allow-origin' => 'Access-Control-Allow-Origin',
		'access-control-expose-headers' => 'Access-Control-Expose-Headers',
		'access-control-max-age' => 'Access-Control-Max-Age',
		'age' => 'Age',
		'clear-site-data' => 'Clear-Site-Data',
		'content-range' => 'Content-Range',
		'content-security-policy' => 'Content-Security-Policy',
		'content-security-policy-report-only' => 'Content-Security-Policy-Report-Only',
		'etag' => 'ETag',
		'expect-ct' => 'Expect-CT',
		'expires' => 'Expires',
		'feature-policy' => 'Feature-Policy',
		'last-modified' => 'Last-Modified',
		'location' => 'Location',
		'proxy-authenticate' => 'Proxy-Authenticate',
		'public-key-pins' => 'Public-Key-Pins',
		'public-key-pins-report-only' => 'Public-Key-Pins-Report-Only',
		'referrer-policy' => 'Referrer-Policy',
		'retry-after' => 'Retry-After',
		'server' => 'Server',
		'set-cookie' => 'Set-Cookie',
		'sourcemap' => 'SourceMap',
		'strict-transport-security' => 'Strict-Transport-Security',
		'timing-allow-origin' => 'Timing-Allow-Origin',
		'tk' => 'Tk',
		'trailer' => 'Trailer',
		'transfer-encoding' => 'Transfer-Encoding',
		'vary' => 'Vary',
		'www-authenticate' => 'WWW-Authenticate',
		'x-content-type-options' => 'X-Content-Type-Options',
		'x-dns-prefetch-control' => 'X-DNS-Prefetch-Control',
		'x-frame-options' => 'X-Frame-Options',
		'x-xss-protection' => 'X-XSS-Protection',
		// WebSocket
		'sec-websocket-extensions' => 'Sec-WebSocket-Extensions',
		'sec-websocket-key' => 'Sec-WebSocket-Key',
		'sec-websocket-protocol' => 'Sec-WebSocket-Protocol',
		'sec-websocket-version' => 'Sec-WebSocket-Version',
		// Custom
		'x-request-id' => 'X-Request-ID',
	];
	/**
	 * Standard Response Status Codes and Reasons.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
	 *
	 * @var array|string[]
	 */
	protected static array $responseStatus = [
		// Information responses
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		103 => 'Early Hints',
		// Successful responses
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		226 => 'IM Used',
		// Redirection messages
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		// Client errors responses
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Payload Too Large',
		414 => 'URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => "I'm a teapot",
		421 => 'Misdirected Request',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		451 => 'Unavailable For Legal Reasons',
		499 => 'Client Closed Request',
		// Server error responses
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		510 => 'Not Extended',
		511 => 'Network Authentication Required',
		599 => 'Network Connect Timeout Error',
	];

	private function makeHeaderIndex(string $name, int $index) : int
	{
		if ($index < 0 && isset($this->headers[$name])) {
			$index = \count($this->headers[$name]) + $index;
		}
		return $index;
	}

	public function countHeader(string $name) : int
	{
		$name = \strtolower($name);
		return empty($this->headers[$name])
			? 0
			: \count($this->headers[$name]);
	}

	public function getHeader(string $name, int $index = -1) : ?string
	{
		$name = \strtolower($name);
		if (empty($this->headers[$name])) {
			return null;
		}
		return $this->headers[$name][$this->makeHeaderIndex($name, $index)] ?? null;
	}

	public function getHeaders(string $name) : array
	{
		return $this->headers[\strtolower($name)] ?? [];
	}

	public function getAllHeaders() : array
	{
		return $this->headers;
	}

	protected function setHeader(string $name, string ...$values)
	{
		$this->headers[\strtolower($name)] = $values;
		return $this;
	}

	protected function addHeader(string $name, string $value)
	{
		$this->headers[\strtolower($name)][] = $value;
		return $this;
	}

	protected function setHeaders(array $headers)
	{
		foreach ($headers as $name => $values) {
			$values = (array) $values;
			$this->setHeader($name, ...$values);
		}
		return $this;
	}

	protected function removeHeader(string $name, int $index = -1)
	{
		$name = \strtolower($name);
		unset($this->headers[$name][$this->makeHeaderIndex($name, $index)]);
		if (empty($this->headers[$name])) {
			unset($this->headers[$name]);
		}
		return $this;
	}

	protected function removeHeaders(string $name)
	{
		unset($this->headers[\strtolower($name)]);
		return $this;
	}

	protected function removeAllHeaders()
	{
		$this->headers = [];
		return $this;
	}

	protected function sendHeaders() : void
	{
		foreach ($this->getAllHeaders() as $name => $values) {
			foreach ($values as $value) {
				$name = static::getHeaderName($name);
				\header("{$name}: {$value}", false);
			}
		}
	}

	public function hasCookie(string $name) : bool
	{
		return (bool) $this->getCookie($name);
	}

	public function getCookie(string $name) : ?Cookie
	{
		return $this->cookies[$name] ?? null;
	}

	/**
	 * @return array|Cookie[]
	 */
	public function getCookies() : array
	{
		return $this->cookies;
	}

	protected function setCookie(Cookie $cookie)
	{
		$this->cookies[$cookie->getName()] = $cookie;
		return $this;
	}

	/**
	 * @param array|Cookie[] $cookies
	 *
	 * @return $this
	 */
	protected function setCookies(array $cookies)
	{
		foreach ($cookies as $cookie) {
			$this->setCookie($cookie);
		}
		return $this;
	}

	protected function removeCookie(string $name)
	{
		unset($this->cookies[$name]);
		return $this;
	}

	/**
	 * @param array|string[] $names
	 *
	 * @return $this
	 */
	protected function removeCookies(array $names)
	{
		foreach ($names as $name) {
			$this->removeCookie($name);
		}
		return $this;
	}

	public function getBody() : string
	{
		return $this->body;
	}

	protected function setBody(string $body)
	{
		$this->body = $body;
		return $this;
	}

	public function getProtocol() : string
	{
		return $this->protocol;
	}

	protected function setProtocol(string $protocol)
	{
		$this->protocol = $protocol;
		return $this;
	}

	/**
	 * Gets the HTTP Request Method.
	 *
	 * @return string normally one of: GET, HEAD, POST, PATCH, PUT, DELETE or OPTIONS
	 */
	protected function getMethod() : string
	{
		return $this->method;
	}

	/**
	 * @param string $method
	 *
	 * @throws \InvalidArgumentException for invalid method
	 *
	 * @return $this
	 */
	protected function setMethod(string $method)
	{
		$valid = \strtoupper($method);
		if ( ! \in_array($valid, [
			'DELETE',
			'GET',
			'HEAD',
			'OPTIONS',
			'PATCH',
			'POST',
			'PUT',
		], true)) {
			throw new \InvalidArgumentException("Invalid HTTP Request Method: {$method}");
		}
		$this->method = $valid;
		return $this;
	}

	/**
	 * Gets the requested URL.
	 *
	 * @return URL
	 */
	protected function getURL() : URL
	{
		return $this->url;
	}

	/**
	 * @param string|URL $url
	 *
	 * @return $this
	 */
	protected function setURL($url)
	{
		if ( ! $url instanceof URL) {
			$url = new URL($url);
		}
		$this->url = $url;
		return $this;
	}

	protected function parseContentType() : ?string
	{
		return \explode(';', $this->getHeader('Content-Type'), 2)[0] ?? null;
	}

	/**
	 * Gets a header name according with the standards.
	 *
	 * @param string $name header name
	 *
	 * @return string
	 */
	public static function getHeaderName(string $name) : string
	{
		return static::$standardHeaders[\strtolower($name)] ?? $name;
	}

	public static function getResponseReason(int $code, string $default = null) : ?string
	{
		return static::$responseStatus[$code] ?? $default;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Glossary/Quality_values
	 * @see https://stackoverflow.com/a/33748742/6027968
	 *
	 * @param string|null $string
	 *
	 * @return array
	 */
	public static function parseQualityValues(?string $string) : array
	{
		if (empty($string)) {
			return [];
		}
		$quality = \array_reduce(
			\explode(',', $string, 20),
			static function ($qualifier, $part) {
				[$value, $priority] = \array_merge(\explode(';q=', $part), [1]);
				$qualifier[\trim($value)] = (float) $priority;
				return $qualifier;
			},
			[]
		);
		\arsort($quality);
		return $quality;
	}
}
