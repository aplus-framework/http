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
    protected string $protocol = Protocol::HTTP_1_1;
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
    protected string $body;
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
        if (Header::isMultiline($headerName)) {
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
        return $this->body ?? '';
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
        $this->protocol = Protocol::validate($protocol);
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
    protected function isMethod(string $method) : bool
    {
        return $this->getMethod() === Method::validate($method);
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
        $this->method = Method::validate($method);
        return $this;
    }

    protected function setStatusCode(int $code) : static
    {
        $this->statusCode = Status::validate($code);
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

    protected function isStatusCode(int $code) : bool
    {
        return $this->getStatusCode() === Status::validate($code);
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
