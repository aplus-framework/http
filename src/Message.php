<?php declare(strict_types=1);
/*
 * This file is part of The Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * Class Message.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages
 * @see https://tools.ietf.org/html/rfc7231
 */
abstract class Message implements MessageInterface
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
	 * @var array<string,Cookie>
	 */
	protected array $cookies = [];
	/**
	 * HTTP Message Headers.
	 *
	 * @var array<string,string>
	 */
	protected array $headers = [];
	/**
	 * Standard Headers.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
	 *
	 * @var array<string,string>
	 */
	protected static array $standardHeaders = [
		// General headers (Request and Response)
		'cache-control' => 'Cache-Control',
		'connection' => 'Connection',
		'content-disposition' => 'Content-Disposition',
		'date' => 'Date',
		'keep-alive' => 'Keep-Alive',
		'link' => 'Link',
		'via' => 'Via',
		'warning' => 'Warning',
		// Representation headers (Request and Response)
		'content-encoding' => 'Content-Encoding',
		'content-language' => 'Content-Language',
		'content-location' => 'Content-Location',
		'content-type' => 'Content-Type',
		// Payload headers (Request and Response)
		'content-length' => 'Content-Length',
		'content-range' => 'Content-Range',
		'trailer' => 'Trailer',
		'transfer-encoding' => 'Transfer-Encoding',
		// Request headers
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
		// Response headers
		'accept-ranges' => 'Accept-Ranges',
		'access-control-allow-credentials' => 'Access-Control-Allow-Credentials',
		'access-control-allow-headers' => 'Access-Control-Allow-Headers',
		'access-control-allow-methods' => 'Access-Control-Allow-Methods',
		'access-control-allow-origin' => 'Access-Control-Allow-Origin',
		'access-control-expose-headers' => 'Access-Control-Expose-Headers',
		'access-control-max-age' => 'Access-Control-Max-Age',
		'age' => 'Age',
		'allow' => 'Allow',
		'clear-site-data' => 'Clear-Site-Data',
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
		'vary' => 'Vary',
		'www-authenticate' => 'WWW-Authenticate',
		'x-content-type-options' => 'X-Content-Type-Options',
		'x-dns-prefetch-control' => 'X-DNS-Prefetch-Control',
		'x-frame-options' => 'X-Frame-Options',
		'x-xss-protection' => 'X-XSS-Protection',
		// Custom (Response)
		'x-request-id' => 'X-Request-ID',
		'x-powered-by' => 'X-Powered-By',
		// WebSocket
		'sec-websocket-extensions' => 'Sec-WebSocket-Extensions',
		'sec-websocket-key' => 'Sec-WebSocket-Key',
		'sec-websocket-protocol' => 'Sec-WebSocket-Protocol',
		'sec-websocket-version' => 'Sec-WebSocket-Version',
	];
	/**
	 * Standard Response Status Codes and Reasons.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
	 *
	 * @var array<int,string>
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
		416 => 'Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => "I'm a teapot",
		421 => 'Misdirected Request',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Too Early',
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

	#[Pure]
	public function hasHeader(string $name) : bool
	{
		return isset($this->headers[\strtolower($name)]);
	}

	#[Pure]
	public function getHeader(string $name) : ?string
	{
		return $this->headers[\strtolower($name)] ?? null;
	}

	/**
	 * @return array<string,string>
	 */
	#[Pure]
	public function getHeaders() : array
	{
		return $this->headers;
	}

	#[Pure]
	public function getHeaderLine(string $name) : ?string
	{
		$value = $this->getHeader($name);
		if ($value === null) {
			return null;
		}
		$name = static::getHeaderName($name);
		return $name . ': ' . $value;
	}

	/**
	 * @return array<int,string>
	 */
	#[Pure]
	public function getHeaderLines() : array
	{
		$lines = [];
		foreach ($this->getHeaders() as $name => $value) {
			$name = static::getHeaderName($name);
			$lines[] = $name . ': ' . $value;
		}
		return $lines;
	}

	/**
	 * Set a Message header.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
	 *
	 * @return static
	 */
	protected function setHeader(string $name, string $value) : static
	{
		$this->headers[\strtolower($name)] = $value;
		return $this;
	}

	/**
	 * Set a list of headers.
	 *
	 * @param array<string,string> $headers
	 *
	 * @return static
	 */
	protected function setHeaders(array $headers) : static
	{
		foreach ($headers as $name => $value) {
			$this->setHeader((string) $name, (string) $value);
		}
		return $this;
	}

	/**
	 * Remove a header by name.
	 *
	 * @param string $name
	 *
	 * @return static
	 */
	protected function removeHeader(string $name) : static
	{
		unset($this->headers[\strtolower($name)]);
		return $this;
	}

	/**
	 * Remove many headers by a list of headers.
	 *
	 * @return static
	 */
	protected function removeHeaders() : static
	{
		$this->headers = [];
		return $this;
	}

	/**
	 * Say if the Message has a Cookie.
	 *
	 * @param string $name Cookie name
	 *
	 * @return bool
	 */
	#[Pure]
	public function hasCookie(string $name) : bool
	{
		return (bool) $this->getCookie($name);
	}

	/**
	 * Get a Cookie by name.
	 *
	 * @param string $name
	 *
	 * @return Cookie|null
	 */
	#[Pure]
	public function getCookie(string $name) : ?Cookie
	{
		return $this->cookies[$name] ?? null;
	}

	/**
	 * Get all Cookies.
	 *
	 * @return array<string,Cookie>
	 */
	#[Pure]
	public function getCookies() : array
	{
		return $this->cookies;
	}

	/**
	 * Set a new Cookie.
	 *
	 * @param Cookie $cookie
	 *
	 * @return static
	 */
	protected function setCookie(Cookie $cookie) : static
	{
		$this->cookies[$cookie->getName()] = $cookie;
		return $this;
	}

	/**
	 * Set a list of Cookies.
	 *
	 * @param array<int,Cookie> $cookies
	 *
	 * @return static
	 */
	protected function setCookies(array $cookies) : static
	{
		foreach ($cookies as $cookie) {
			$this->setCookie($cookie);
		}
		return $this;
	}

	/**
	 * Remove a Cookie by name.
	 *
	 * @param string $name
	 *
	 * @return static
	 */
	protected function removeCookie(string $name) : static
	{
		unset($this->cookies[$name]);
		return $this;
	}

	/**
	 * Remove many Cookies by names.
	 *
	 * @param array<int,string> $names
	 *
	 * @return static
	 */
	protected function removeCookies(array $names) : static
	{
		foreach ($names as $name) {
			$this->removeCookie($name);
		}
		return $this;
	}

	/**
	 * Get the Message body.
	 *
	 * @return string
	 */
	#[Pure]
	public function getBody() : string
	{
		return $this->body;
	}

	/**
	 * Set the Message body.
	 *
	 * @param string $body
	 *
	 * @return static
	 */
	protected function setBody(string $body) : static
	{
		$this->body = $body;
		return $this;
	}

	/**
	 * Get the HTTP protocol.
	 *
	 * @return string
	 */
	#[Pure]
	public function getProtocol() : string
	{
		return $this->protocol;
	}

	/**
	 * Set the HTTP protocol.
	 *
	 * @param string $protocol HTTP/1.1 or HTTP/2
	 *
	 * @return static
	 */
	protected function setProtocol(string $protocol) : static
	{
		$this->protocol = $protocol;
		return $this;
	}

	/**
	 * Gets the HTTP Request Method.
	 *
	 * @return string normally one of: GET, HEAD, POST, PATCH, PUT, DELETE or OPTIONS
	 */
	#[Pure]
	protected function getMethod() : string
	{
		return $this->method;
	}

	/**
	 * Set the request method.
	 *
	 * @param string $method One of: 'DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT'
	 *
	 * @throws InvalidArgumentException for invalid method
	 *
	 * @return static
	 */
	protected function setMethod(string $method) : static
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
			throw new InvalidArgumentException("Invalid HTTP Request Method: {$method}");
		}
		$this->method = $valid;
		return $this;
	}

	/**
	 * Gets the requested URL.
	 *
	 * @return URL
	 */
	#[Pure]
	protected function getURL() : URL
	{
		return $this->url;
	}

	/**
	 * Set the Message URL.
	 *
	 * @param string|URL $url
	 *
	 * @return static
	 */
	protected function setURL(string | URL $url) : static
	{
		if ( ! $url instanceof URL) {
			$url = new URL($url);
		}
		$this->url = $url;
		return $this;
	}

	#[Pure]
	protected function parseContentType() : ?string
	{
		$contentType = $this->getHeader('Content-Type');
		if ($contentType === null) {
			return null;
		}
		$contentType = \explode(';', $contentType, 2)[0];
		return \trim($contentType);
	}

	/**
	 * Gets a header name according with the standards.
	 *
	 * @param string $name header name
	 *
	 * @return string
	 */
	#[Pure]
	public static function getHeaderName(string $name) : string
	{
		return static::$standardHeaders[\strtolower($name)] ?? $name;
	}

	/**
	 * Get the Response reason based on status code.
	 *
	 * @param int $code
	 * @param string|null $default
	 *
	 * @return string|null
	 */
	#[Pure]
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
	 * @return array<string,float>
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
				$qualifier[\trim((string) $value)] = (float) $priority;
				return $qualifier;
			},
			[]
		);
		\arsort($quality);
		return $quality;
	}
}
