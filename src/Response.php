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
use Override;

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
    protected bool $headersSent = false;
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
    protected CSP $csp;
    protected CSP $cspReportOnly;
    protected bool $replaceHeaders = false;

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

    #[Override]
    public function __toString() : string
    {
        if ($this->getHeader(ResponseHeader::DATE) === null) {
            $this->setDate();
        }
        if ($this->getHeader(ResponseHeader::CONTENT_TYPE) === null
            && $this->getBody() !== '') {
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
    #[Override]
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
    #[Override]
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

    #[Override]
    public function setCookie(Cookie $cookie) : static
    {
        return parent::setCookie($cookie);
    }

    #[Override]
    public function setCookies(array $cookies) : static
    {
        return parent::setCookies($cookies);
    }

    #[Override]
    public function removeCookie(string $name) : static
    {
        return parent::removeCookie($name);
    }

    #[Override]
    public function removeCookies(array $names) : static
    {
        return parent::removeCookies($names);
    }

    #[Override]
    public function setHeader(string $name, string $value) : static
    {
        return parent::setHeader($name, $value);
    }

    #[Override]
    public function setHeaders(array $headers) : static
    {
        return parent::setHeaders($headers);
    }

    #[Override]
    public function appendHeader(string $name, string $value) : static
    {
        return parent::appendHeader($name, $value);
    }

    /**
     * Remove a header by name.
     *
     * NOTE: This method does not remove headers set by PHP, such as with
     * sessions or the `header()` function. To do this type of removal use the
     * `header_remove()` function.
     *
     * @see https://www.php.net/manual/en/function.header-remove.php
     *
     * @param string $name
     *
     * @return static
     */
    #[Override]
    public function removeHeader(string $name) : static
    {
        return parent::removeHeader($name);
    }

    #[Override]
    public function removeHeaders() : static
    {
        return parent::removeHeaders();
    }

    /**
     * @param array<string> $names
     *
     * @see Response::removeHeader()
     *
     * @return static
     */
    #[Override]
    public function removeHeadersByNames(array $names) : static
    {
        return parent::removeHeadersByNames($names);
    }

    /**
     * Set the Content-Security-Policy instance.
     *
     * @param CSP $csp
     *
     * @return static
     */
    public function setCsp(CSP $csp) : static
    {
        $this->csp = $csp;
        return $this;
    }

    /**
     * Set the Content-Security-Policy-Report-Only instance.
     *
     * @param CSP $csp
     *
     * @return static
     */
    public function setCspReportOnly(CSP $csp) : static
    {
        $this->cspReportOnly = $csp;
        return $this;
    }

    /**
     * Get the Content-Security-Policy instance or null.
     *
     * @return CSP|null
     */
    public function getCsp() : ?CSP
    {
        return $this->csp ?? null;
    }

    /**
     * Get the Content-Security-Policy-Report-Only instance or null.
     *
     * @return CSP|null
     */
    public function getCspReportOnly() : ?CSP
    {
        return $this->cspReportOnly ?? null;
    }

    /**
     * Tells if the Content-Security-Policy instance has been set.
     *
     * @return bool
     */
    public function hasCsp() : bool
    {
        return isset($this->csp);
    }

    /**
     * Tells if the Content-Security-Policy-Report-Only instance has been set.
     *
     * @return bool
     */
    public function hasCspReportOnly() : bool
    {
        return isset($this->cspReportOnly);
    }

    /**
     * Remove the Content-Security-Policy instance.
     *
     * @return static
     */
    public function removeCsp() : static
    {
        unset($this->csp);
        return $this;
    }

    /**
     * Remove the Content-Security-Policy-Report-Only instance.
     *
     * @return static
     */
    public function removeCspReportOnly() : static
    {
        unset($this->cspReportOnly);
        return $this;
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
    public function setStatus(int $code, ?string $reason = null) : static
    {
        $this->setStatusCode($code);
        $this->setStatusReason(Status::getReason($code, $reason));
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
    #[Override]
    public function setStatusCode(int $code) : static
    {
        return parent::setStatusCode($code);
    }

    /**
     * Get the status code.
     *
     * @return int
     */
    #[Override]
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
    #[Override]
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
    public function redirect(string $location, array $data = [], ?int $code = null) : static
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
        if ($this->headersSent || \headers_sent()) {
            throw new LogicException('Headers are already sent');
        }
        if ($this->getHeader(ResponseHeader::DATE) === null) {
            $this->setDate();
        }
        if ($this->getHeader(ResponseHeader::CONTENT_TYPE) === null) {
            $this->negotiateContentType();
        }
        if ($this->isAutoEtag() && !$this->hasDownload()) {
            $this->negotiateEtag();
        }
        $this->negotiateCsp();
        \header($this->getStartLine());
        $replace = $this->isReplacingHeaders();
        foreach ($this->getHeaderLines() as $line) {
            \header($line, $replace);
        }
        $this->headersSent = true;
    }

    /**
     * The replace parameter indicates whether the header should replace a
     * previous similar header, or add a next header of the same type.
     * By default, it will replace, but if you pass in false as the first
     * argument you can force multiple headers of the same type.
     *
     * @param bool $replace
     *
     * @see Response::sendHeaders()
     *
     * @return static
     */
    public function setReplaceHeaders(bool $replace = true) : static
    {
        $this->replaceHeaders = $replace;
        return $this;
    }

    /**
     * Tells if headers are being replaced.
     *
     * @see Response::setReplaceHeaders()
     *
     * @return bool
     */
    public function isReplacingHeaders() : bool
    {
        return $this->replaceHeaders;
    }

    /**
     * Set the Content-Security-Policy and Content-Security-Policy-Report-Only
     * headers if the CSP classes are set and the response has not downloads.
     *
     * @return void
     */
    protected function negotiateCsp() : void
    {
        $csp = $this->getCsp();
        if ($csp && !$this->hasDownload()) {
            $this->setHeader(
                ResponseHeader::CONTENT_SECURITY_POLICY,
                $csp->render()
            );
        }
        $csp = $this->getCspReportOnly();
        if ($csp && !$this->hasDownload()) {
            $this->setHeader(
                ResponseHeader::CONTENT_SECURITY_POLICY_REPORT_ONLY,
                $csp->render()
            );
        }
    }

    /**
     * Negotiates the Content-Type header, setting the MIME type "text/html" if
     * the response body is not empty.
     *
     * If the response body is empty, it removes the Content-Type header,
     * and it will not appear to the client from the request.
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
        \ini_set('default_mimetype', '');
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
        $this->setContentLength();
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
     * @param int|null $flags [optional] <p>
     *  Bitmask consisting of <b>JSON_FORCE_OBJECT</b>,
     *  <b>JSON_HEX_AMP</b>,
     *  <b>JSON_HEX_APOS</b>,
     *  <b>JSON_HEX_QUOT</b>,
     *  <b>JSON_HEX_TAG</b>,
     *  <b>JSON_INVALID_UTF8_IGNORE</b>,
     *  <b>JSON_INVALID_UTF8_SUBSTITUTE</b>,
     *  <b>JSON_INVALID_UTF8_SUBSTITUTE</b>,
     *  <b>JSON_NUMERIC_CHECK</b>,
     *  <b>JSON_PARTIAL_OUTPUT_ON_ERROR</b>,
     *  <b>JSON_PRESERVE_ZERO_FRACTION</b>,
     *  <b>JSON_PRETTY_PRINT</b>,
     *  <b>JSON_THROW_ON_ERROR</b>.
     *  <b>JSON_UNESCAPED_LINE_TERMINATORS</b>,
     *  <b>JSON_UNESCAPED_SLASHES</b>,
     *  <b>JSON_UNESCAPED_UNICODE</b>,
     *  </p>
     *  <p>Default is <b>JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE</b>
     *  when null. <b>JSON_THROW_ON_ERROR</b> is enforced by default.</p>
     * @param int<1,max> $depth Set the maximum depth. Must be greater than zero.
     *
     * @see https://www.php.net/manual/en/function.json-encode.php
     * @see https://www.php.net/manual/en/json.constants.php
     *
     * @throws JsonException if json_encode() fails
     *
     * @return static
     */
    public function setJson(mixed $data, ?int $flags = null, int $depth = 512) : static
    {
        if ($flags === null) {
            $flags = $this->getJsonFlags();
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
            ResponseHeader::CACHE_CONTROL,
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
            ResponseHeader::CACHE_CONTROL,
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
    public function setAutoEtag(bool $active = true, ?string $hashAlgo = null) : static
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
            ResponseHeader::CONTENT_TYPE,
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
        $this->setHeader(ResponseHeader::CONTENT_LANGUAGE, $language);
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
        $this->setHeader(ResponseHeader::CONTENT_ENCODING, $encoding);
        return $this;
    }

    /**
     * Set the Content-Length header.
     *
     * @param int|null $length Set a value or leave null to set the body length automatically
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Length
     *
     * @return static
     */
    public function setContentLength(?int $length = null) : static
    {
        $length ??= \strlen($this->getBody());
        $this->setHeader(ResponseHeader::CONTENT_LENGTH, (string) $length);
        return $this;
    }

    /**
     * Set the Date header.
     *
     * @param DateTime $datetime A DateTime value or default to the current DateTime
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Date
     *
     * @return static
     */
    public function setDate(DateTime $datetime = new DateTime()) : static
    {
        $date = clone $datetime;
        $date->setTimezone(new DateTimeZone('UTC'));
        $this->setHeader(
            ResponseHeader::DATE,
            $date->format(DateTimeInterface::RFC7231)
        );
        return $this;
    }

    /**
     * Set the ETag header.
     *
     * @param string $etag The ETag value without the quotes around it
     * @param bool $strong False for a weak validator, otherwise true
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
     * @param DateTime $datetime A DateTime value or default to the current DateTime
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expires
     *
     * @return static
     */
    public function setExpires(DateTime $datetime = new DateTime()) : static
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
     * @param DateTime $datetime A DateTime value or default to the current DateTime
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified
     *
     * @return static
     */
    public function setLastModified(DateTime $datetime = new DateTime()) : static
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
