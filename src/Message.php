<?php namespace Framework\HTTP;

/**
 * Class Message.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages
 */
abstract class Message
{
	/**
	 * HTTP Protocol.
	 *
	 * @var string
	 */
	protected $protocol = 'HTTP/1.1';
	/**
	 * HTTP Message Body.
	 *
	 * @var string
	 */
	protected $body = '';
	/**
	 * HTTP Message Cookies.
	 *
	 * @var array|Cookie[]
	 */
	protected $cookies = [];
	/**
	 * HTTP Message Headers.
	 *
	 * @var array
	 */
	protected $headers = [];
	/**
	 * Standard Headers.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
	 *
	 * @var array
	 */
	protected static $standardHeaders = [
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

	public function getHeader(string $name) : ?string
	{
		return $this->headers[static::getHeaderName($name)] ?? null;
	}

	public function getHeaders() : array
	{
		return $this->headers;
	}

	protected function setHeader(string $name, string $value)
	{
		$this->headers[static::getHeaderName($name)] = $value;
		return $this;
	}

	protected function setHeaders(array $headers)
	{
		foreach ($headers as $name => $value) {
			$this->setHeader($name, $value);
		}
		return $this;
	}

	protected function removeHeader(string $name)
	{
		unset($this->headers[static::getHeaderName($name)]);
		return $this;
	}

	protected function removeHeaders(array $names)
	{
		foreach ($names as $name) {
			$this->removeHeader($name);
		}
		return $this;
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
	 * Gets a header name according with the standards.
	 *
	 * @param string $name header name
	 *
	 * @return string
	 */
	protected static function getHeaderName(string $name) : string
	{
		return static::$standardHeaders[\strtolower($name)] ?? $name;
	}
}
