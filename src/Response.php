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

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Framework\HTTP\Debug\HTTPCollector;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use JsonException;
use LogicException;

/**
 * Class Response.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#HTTP_Responses
 *
 * @package http
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
    protected int $statusCode = Status::OK;
    /**
     * HTTP Response Status Reason.
     */
    protected string $statusReason = 'OK';
    protected ?string $sendedBody = null;
    protected bool $inToString = false;
    protected bool $autoEtag = false;
    protected string $autoEtagHashAlgo = 'xxh3';
    protected HTTPCollector $debugCollector;

    /**
     * Response constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->setProtocol($this->request->getProtocol());
    }

    public function __toString() : string
    {
        if ($this->getHeader(Header::DATE) === null) {
            $this->setDate(new DateTime());
        }
        if ($this->getHeader(Header::CONTENT_TYPE) === null) {
            $this->setContentType('text/html');
        }
        if ($this->hasDownload()) {
            $this->inToString = true;
            $this->sendDownload();
            $this->inToString = false;
        }
        return parent::__toString();
    }

    /**
     * @return Request
     */
    #[Pure]
    public function getRequest() : Request
    {
        return $this->request;
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
        return $this->body = parent::getBody() . $buffer;
    }

    /**
     * Set the Response body.
     *
     * @param string $body
     *
     * @return static
     */
    public function setBody(string $body) : static
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
     * @return static
     */
    public function prependBody(string $content) : static
    {
        return parent::setBody($content . $this->getBody());
    }

    /**
     * Append a string to the body.
     *
     * @param string $content
     *
     * @return static
     */
    public function appendBody(string $content) : static
    {
        return parent::setBody($this->getBody() . $content);
    }

    public function setCookie(Cookie $cookie) : static
    {
        return parent::setCookie($cookie);
    }

    public function setCookies(array $cookies) : static
    {
        return parent::setCookies($cookies);
    }

    public function removeCookie(string $name) : static
    {
        return parent::removeCookie($name);
    }

    public function removeCookies(array $names) : static
    {
        return parent::removeCookies($names);
    }

    public function setHeader(string $name, string $value) : static
    {
        return parent::setHeader($name, $value);
    }

    public function setHeaders(array $headers) : static
    {
        return parent::setHeaders($headers);
    }

    public function appendHeader(string $name, string $value) : static
    {
        return parent::appendHeader($name, $value);
    }

    public function removeHeader(string $name) : static
    {
        return parent::removeHeader($name);
    }

    public function removeHeaders() : static
    {
        return parent::removeHeaders();
    }

    /**
     * Get the status line without the protocol part.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#status_line
     *
     * @return string
     */
    #[Pure]
    public function getStatus() : string
    {
        return "{$this->statusCode} {$this->statusReason}";
    }

    /**
     * Set the status part in the start-line.
     *
     * @param int $code
     * @param string|null $reason
     *
     * @throws InvalidArgumentException if status code is invalid
     * @throws LogicException is status code is unknown and a reason is not set
     *
     * @return static
     */
    public function setStatus(int $code, string $reason = null) : static
    {
        $this->setStatusCode($code);
        $reason ?: $reason = Status::getReason($code);
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
     * @return static
     */
    public function setStatusCode(int $code) : static
    {
        return parent::setStatusCode($code);
    }

    /**
     * Get the status code.
     *
     * @return int
     */
    #[Pure]
    public function getStatusCode() : int
    {
        return parent::getStatusCode();
    }

    /**
     * @param int $code
     *
     * @throws InvalidArgumentException if status code is invalid
     *
     * @return bool
     */
    public function isStatusCode(int $code) : bool
    {
        return parent::isStatusCode($code);
    }

    /**
     * Set a custom status reason.
     *
     * @param string $reason
     *
     * @return static
     */
    public function setStatusReason(string $reason) : static
    {
        $this->statusReason = $reason;
        return $this;
    }

    /**
     * Get the status reason.
     *
     * @return string
     */
    #[Pure]
    public function getStatusReason() : string
    {
        return $this->statusReason;
    }

    /**
     * Say if the response was sent.
     *
     * @return bool
     */
    #[Pure]
    public function isSent() : bool
    {
        return $this->isSent;
    }

    /**
     * Sets the HTTP Redirect Response with data accessible in the next HTTP Request.
     *
     * @param string $location Location Header value
     * @param array|mixed[] $data Session data available on next Request
     * @param int|null $code HTTP Redirect status code. Leave null to determine
     * based on the current HTTP method.
     *
     * @see http://en.wikipedia.org/wiki/Post/Redirect/Get
     * @see Request::getRedirectData()
     *
     * @throws InvalidArgumentException for invalid Redirection code
     * @throws LogicException if PHP Session is not active to set redirect data
     *
     * @return static
     */
    public function redirect(string $location, array $data = [], int $code = null) : static
    {
        if ($code === null) {
            $code = $this->request->getMethod() === Method::GET
                ? Status::TEMPORARY_REDIRECT
                : Status::SEE_OTHER;
        } elseif ($code < 300 || $code > 399) {
            throw new InvalidArgumentException("Invalid Redirection code: {$code}");
        }
        $this->setStatus($code);
        $this->setHeader(ResponseHeader::LOCATION, $location);
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
     * @throws LogicException if Response is already sent
     */
    public function send() : void
    {
        if (isset($this->debugCollector)) {
            $start = \microtime(true);
            $this->sendAll();
            $end = \microtime(true);
            $this->debugCollector->addData([
                'start' => $start,
                'end' => $end,
                'message' => 'response',
                'type' => 'send',
            ]);
            return;
        }
        $this->sendAll();
    }

    protected function sendAll() : void
    {
        if ($this->isSent) {
            throw new LogicException('Response is already sent');
        }
        $this->sendHeaders();
        $this->sendCookies();
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
     * Send the HTTP headers to the output.
     *
     * @throws LogicException if headers are already sent
     */
    protected function sendHeaders() : void
    {
        if (\headers_sent()) {
            throw new LogicException('Headers are already sent');
        }
        if ($this->getHeader(Header::DATE) === null) {
            $this->setDate(new DateTime());
        }
        if ($this->getHeader(Header::CONTENT_TYPE) === null) {
            $this->negotiateContentType();
        }
        if ($this->isAutoEtag() && ! $this->hasDownload()) {
            $this->negotiateEtag();
        }
        \header($this->getStartLine());
        foreach ($this->getHeaderLines() as $line) {
            \header($line);
        }
    }

    /**
     * Negotiates the Content-Type header, setting the MIME type "text/html" if
     * the response body is not empty.
     *
     * If the response body is empty, it checks servers to set the header to an
     * empty value, which causes the server to remove the Content-Type header,
     * and it will not appear to the client from the request.
     *
     * The header will also not be set on the PHP Development Server when the
     * body is empty.
     *
     * This prevents the Content-Type from appearing without it being needed in,
     * for example, REST API responses.
     *
     * @see https://stackoverflow.com/a/21029402/6027968
     */
    protected function negotiateContentType() : void
    {
        if ($this->getBody() !== '') {
            $this->setContentType('text/html');
            return;
        }
        $software = (string) $this->getRequest()->getServer('SERVER_SOFTWARE');
        $software = \strtolower($software);
        // These servers remove headers if they are set to an empty value:
        $servers = [
            'apache',
            'lighttpd',
            'nginx',
        ];
        foreach ($servers as $server) {
            if (\str_contains($software, $server)) {
                $this->setHeader(Header::CONTENT_TYPE, '');
                return;
            }
        }
        // Prevent PHP Development Server from setting the default Content-Type:
        if (\str_contains($software, 'php')) {
            \ini_set('default_mimetype', '');
        }
    }

    /**
     * Set the ETag header, based on the Response body, and start the
     * negotiation.
     *
     * - Empty the body and set a status 304 (Not Modified) if the Request
     * If-None-Match header has the same value of the generated ETag, on GET and
     * HEAD requests.
     *
     * - Empty the body and set a status 412 (Precondition Failed) if the Request
     * If-Match header is set and has not the same value of the generated ETag,
     * on non-GET or non-HEAD requests.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag
     */
    protected function negotiateEtag() : void
    {
        // Content-Length is required by Firefox,
        // otherwise it does not send the If-None-Match header
        $this->setContentLength(\strlen($this->getBody()));
        $etag = \hash($this->autoEtagHashAlgo, $this->getBody());
        $this->setEtag($etag);
        $etag = '"' . $etag . '"';
        // Cache of unchanged resources:
        $ifNoneMatch = $this->getRequest()->getHeader(RequestHeader::IF_NONE_MATCH);
        if ($ifNoneMatch !== null
            && ($ifNoneMatch === $etag || $ifNoneMatch === 'W/' . $etag)
            && \in_array(
                $this->getRequest()->getMethod(),
                [Method::GET, Method::HEAD],
                true
            )
        ) {
            $this->setNotModified();
            $this->setBody('');
            return;
        }
        // Avoid mid-air collisions:
        $ifMatch = $this->getRequest()->getHeader(RequestHeader::IF_MATCH);
        if ($ifMatch !== null && $ifMatch !== $etag) {
            $this->setBody('');
            $this->setStatus(Status::PRECONDITION_FAILED);
        }
    }

    /**
     * Set Response body and Content-Type as JSON.
     *
     * @param mixed $data The data being encoded. Can be any type except
     * a resource.
     * @param int|null $flags <p>
     * Bitmask consisting of
     * {@see \JSON_HEX_QUOT}<br/>
     * {@see \JSON_HEX_TAG}<br/>
     * {@see \JSON_HEX_AMP}<br/>
     * {@see \JSON_HEX_APOS}<br/>
     * {@see \JSON_NUMERIC_CHECK}<br/>
     * {@see \JSON_PRETTY_PRINT}<br/>
     * {@see \JSON_UNESCAPED_SLASHES}<br/>
     * {@see \JSON_FORCE_OBJECT}<br/>
     * {@see \JSON_UNESCAPED_UNICODE}<br/>
     * {@see \JSON_THROW_ON_ERROR}<br/>
     * The behaviour of these constants is described on the JSON constants page.
     * </p>
     * <p>Default is <b>JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE</b>
     * when null. Set 0 to do not use none.</p>
     * @param int<1,max> $depth Set the maximum depth. Must be greater than zero.
     *
     * @see https://www.php.net/manual/en/function.json-encode.php
     * @see https://www.php.net/manual/en/json.constants.php
     *
     * @throws JsonException if json_encode() fails
     *
     * @return static
     */
    public function setJson(mixed $data, int $flags = null, int $depth = 512) : static
    {
        if ($flags === null) {
            $flags = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;
        }
        $data = \json_encode($data, $flags | \JSON_THROW_ON_ERROR, $depth);
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
     * @param int $seconds
     * @param bool $public
     *
     * @return static
     */
    public function setCache(int $seconds, bool $public = false) : static
    {
        $date = new DateTime();
        $date->modify('+' . $seconds . ' seconds');
        $this->setExpires($date);
        $this->setHeader(
            Header::CACHE_CONTROL,
            ($public ? 'public' : 'private') . ', max-age=' . $seconds
        );
        $this->cacheSeconds = $seconds;
        return $this;
    }

    /**
     * Clear the browser cache.
     *
     * @return static
     */
    public function setNoCache() : static
    {
        $this->setHeader(
            Header::CACHE_CONTROL,
            'no-cache, no-store, max-age=0'
        );
        $this->cacheSeconds = 0;
        return $this;
    }

    /**
     * Get the number of seconds the cache is active.
     *
     * @return int
     */
    #[Pure]
    public function getCacheSeconds() : int
    {
        return $this->cacheSeconds;
    }

    /**
     * Enable or disable the capability of auto-add the ETag header and
     * negotiate the response with it.
     *
     * @param bool $active
     * @param string|null $hashAlgo
     *
     * @see Response::negotiateEtag()
     *
     * @return static
     */
    public function setAutoEtag(bool $active = true, string $hashAlgo = null) : static
    {
        $this->autoEtag = $active;
        if ($hashAlgo !== null) {
            $this->autoEtagHashAlgo = $hashAlgo;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoEtag() : bool
    {
        return $this->autoEtag;
    }

    /**
     * Set the Content-Type header.
     *
     * @param string $mime
     * @param string $charset
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
     *
     * @return static
     */
    public function setContentType(string $mime, string $charset = 'UTF-8') : static
    {
        $this->setHeader(
            Header::CONTENT_TYPE,
            $mime . ($charset ? '; charset=' . $charset : '')
        );
        return $this;
    }

    /**
     * Set the Content-Language header.
     *
     * @param string $language
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Language
     *
     * @return static
     */
    public function setContentLanguage(string $language) : static
    {
        $this->setHeader(Header::CONTENT_LANGUAGE, $language);
        return $this;
    }

    /**
     * Set the Content-Encoding header.
     *
     * @param string $encoding
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Encoding
     *
     * @return static
     */
    public function setContentEncoding(string $encoding) : static
    {
        $this->setHeader(Header::CONTENT_ENCODING, $encoding);
        return $this;
    }

    /**
     * Set the Content-Length header.
     *
     * @param int $length
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Length
     *
     * @return static
     */
    public function setContentLength(int $length) : static
    {
        $this->setHeader(Header::CONTENT_LENGTH, (string) $length);
        return $this;
    }

    /**
     * Set the Date header.
     *
     * @param DateTime $datetime
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Date
     *
     * @return static
     */
    public function setDate(DateTime $datetime) : static
    {
        $date = clone $datetime;
        $date->setTimezone(new DateTimeZone('UTC'));
        $this->setHeader(
            Header::DATE,
            $date->format(DateTimeInterface::RFC7231)
        );
        return $this;
    }

    /**
     * Set the ETag header.
     *
     * @param string $etag
     * @param bool $strong
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag
     *
     * @return static
     */
    public function setEtag(string $etag, bool $strong = true) : static
    {
        $etag = '"' . $etag . '"';
        if ($strong === false) {
            $etag = 'W/' . $etag;
        }
        $this->setHeader(ResponseHeader::ETAG, $etag);
        return $this;
    }

    /**
     * Set the Expires header.
     *
     * @param DateTime $datetime
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expires
     *
     * @return static
     */
    public function setExpires(DateTime $datetime) : static
    {
        $date = clone $datetime;
        $date->setTimezone(new DateTimeZone('UTC'));
        $this->setHeader(
            ResponseHeader::EXPIRES,
            $date->format(DateTimeInterface::RFC7231)
        );
        return $this;
    }

    /**
     * Set the Last-Modified header.
     *
     * @param DateTime $datetime
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified
     *
     * @return static
     */
    public function setLastModified(DateTime $datetime) : static
    {
        $date = clone $datetime;
        $date->setTimezone(new DateTimeZone('UTC'));
        $this->setHeader(
            ResponseHeader::LAST_MODIFIED,
            $date->format(DateTimeInterface::RFC7231)
        );
        return $this;
    }

    /**
     * Set the status line as "Not Modified".
     *
     * @return static
     */
    public function setNotModified() : static
    {
        $this->setStatus(Status::NOT_MODIFIED);
        return $this;
    }

    public function setDebugCollector(HTTPCollector $debugCollector) : static
    {
        $this->debugCollector = $debugCollector;
        $this->debugCollector->setResponse($this);
        return $this;
    }
}
