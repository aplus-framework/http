<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP;

use BadMethodCallException;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * Class Message.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages
 * @see https://datatracker.ietf.org/doc/html/rfc7231
 *
 * @package http
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
     * HTTP Response Status Code.
     */
    protected int $statusCode;
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
        'pragma' => 'Pragma',
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
        'sec-fetch-dest' => 'Sec-Fetch-Dest',
        'sec-fetch-mode' => 'Sec-Fetch-Mode',
        'sec-fetch-site' => 'Sec-Fetch-Site',
        'sec-fetch-user' => 'Sec-Fetch-User',
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

    public function __toString() : string
    {
        $eol = "\r\n";
        $message = $this->getStartLine() . $eol;
        foreach ($this->getHeaderLines() as $headerLine) {
            $message .= $headerLine . $eol;
        }
        $message .= $eol;
        $message .= $this->getBody();
        return $message;
    }

    /**
     * Get the Message Start-Line.
     *
     * @throws BadMethodCallException if $this is not an instance of
     * RequestInterface or ResponseInterface
     *
     * @return string
     */
    public function getStartLine() : string
    {
        if ($this instanceof RequestInterface) {
            $query = $this->getUrl()->getQuery();
            $query = ($query !== null && $query !== '') ? '?' . $query : '';
            return $this->getMethod()
                . ' ' . $this->getUrl()->getPath() . $query
                . ' ' . $this->getProtocol();
        }
        if ($this instanceof ResponseInterface) {
            return $this->getProtocol()
                . ' ' . $this->getStatus();
        }
        throw new BadMethodCallException(
            static::class . ' is not an instance of ' . RequestInterface::class
            . ' or ' . ResponseInterface::class
        );
    }

    #[Pure]
    public function hasHeader(string $name, string $value = null) : bool
    {
        return $value === null
            ? $this->getHeader($name) !== null
            : $this->getHeader($name) === $value;
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
        $name = Header::getName($name);
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
            $name = Header::getName($name);
            if (\str_contains($value, "\n")) {
                foreach (\explode("\n", $value) as $val) {
                    $lines[] = $name . ': ' . $val;
                }
                continue;
            }
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
     * Append a Message header.
     *
     * Used to set repeated header field names.
     *
     * @param string $name
     * @param string $value
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
     *
     * @return static
     */
    protected function appendHeader(string $name, string $value) : static
    {
        $current = $this->getHeader($name);
        if ($current !== null) {
            $separator = $this->getHeaderValueSeparator($name);
            $value = $current . $separator . $value;
        }
        $this->setHeader($name, $value);
        return $this;
    }

    /**
     * @param string $headerName
     *
     * @see https://stackoverflow.com/a/38406581/6027968
     *
     * @return string
     */
    private function getHeaderValueSeparator(string $headerName) : string
    {
        $headerName = \strtolower($headerName);
        if (\in_array($headerName, \array_map('\strtolower', [
            MessageInterface::HEADER_DATE,
            RequestInterface::HEADER_IF_MODIFIED_SINCE,
            RequestInterface::HEADER_IF_RANGE,
            RequestInterface::HEADER_IF_UNMODIFIED_SINCE,
            ResponseInterface::HEADER_EXPIRES,
            ResponseInterface::HEADER_LAST_MODIFIED,
            ResponseInterface::HEADER_PROXY_AUTHENTICATE,
            ResponseInterface::HEADER_RETRY_AFTER,
            ResponseInterface::HEADER_SET_COOKIE,
            ResponseInterface::HEADER_WWW_AUTHENTICATE,
        ]), true)) {
            return "\n";
        }
        return ', ';
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
     * @param string $protocol HTTP/1.1, HTTP/2, etc
     *
     * @return static
     */
    protected function setProtocol(string $protocol) : static
    {
        $valid = \strtoupper($protocol);
        if ( ! \in_array($valid, [
            static::PROTOCOL_HTTP_1_0,
            static::PROTOCOL_HTTP_1_1,
            static::PROTOCOL_HTTP_2_0,
            static::PROTOCOL_HTTP_2,
            static::PROTOCOL_HTTP_3,
        ], true)) {
            throw new InvalidArgumentException("Invalid HTTP Protocol: {$protocol}");
        }
        $this->protocol = $valid;
        return $this;
    }

    /**
     * Gets the HTTP Request Method.
     *
     * @return string $method One of: CONNECT, DELETE, GET, HEAD, OPTIONS,
     * PATCH, POST, PUT, or TRACE
     */
    #[Pure]
    protected function getMethod() : string
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @throws InvalidArgumentException for invalid method
     *
     * @return bool
     */
    protected function hasMethod(string $method) : bool
    {
        return $this->getMethod() === $this->makeMethod($method);
    }

    /**
     * Set the request method.
     *
     * @param string $method One of: CONNECT, DELETE, GET, HEAD, OPTIONS, PATCH,
     * POST, PUT, or TRACE
     *
     * @throws InvalidArgumentException for invalid method
     *
     * @return static
     */
    protected function setMethod(string $method) : static
    {
        $this->method = $this->makeMethod($method);
        return $this;
    }

    protected function makeMethod(string $method) : string
    {
        $valid = \strtoupper($method);
        if ( ! \in_array($valid, [
            RequestInterface::METHOD_CONNECT,
            RequestInterface::METHOD_DELETE,
            RequestInterface::METHOD_GET,
            RequestInterface::METHOD_HEAD,
            RequestInterface::METHOD_OPTIONS,
            RequestInterface::METHOD_PATCH,
            RequestInterface::METHOD_POST,
            RequestInterface::METHOD_PUT,
            RequestInterface::METHOD_TRACE,
        ], true)) {
            throw new InvalidArgumentException('Invalid HTTP Request Method: ' . $method);
        }
        return $valid;
    }

    protected function setStatusCode(int $code) : static
    {
        $this->statusCode = $this->makeStatusCode($code);
        return $this;
    }

    /**
     * Get the status code.
     *
     * @return int
     */
    #[Pure]
    protected function getStatusCode() : int
    {
        return $this->statusCode;
    }

    protected function hasStatusCode(int $code) : bool
    {
        return $this->getStatusCode() === $this->makeStatusCode($code);
    }

    protected function makeStatusCode(int $code) : int
    {
        if ($code < 100 || $code > 599) {
            throw new InvalidArgumentException("Invalid status code: {$code}");
        }
        return $code;
    }

    /**
     * Gets the requested URL.
     *
     * @return URL
     */
    #[Pure]
    protected function getUrl() : URL
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
    protected function setUrl(string | URL $url) : static
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

    public static function setHeaderName(string $name) : void
    {
        static::$standardHeaders[\strtolower($name)] = $name;
    }

    /**
     * Get a Response reason based on status code.
     *
     * @param int $code
     * @param string|null $default
     *
     * @return string|null
     */
    #[Pure]
    protected static function getReasonByCode(int $code, string $default = null) : ?string
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
