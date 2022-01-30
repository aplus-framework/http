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

use Stringable;

/**
 * Interface MessageInterface.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP
 *
 * @package http
 */
interface MessageInterface extends Stringable
{
    /**
     * @see https://en.wikipedia.org/wiki/HTTP/1.0
     *
     * @var string
     */
    public const PROTOCOL_HTTP_1_0 = 'HTTP/1.0';
    /**
     * @see https://en.wikipedia.org/wiki/HTTP/1.1
     *
     * @var string
     */
    public const PROTOCOL_HTTP_1_1 = 'HTTP/1.1';
    /**
     * @see https://en.wikipedia.org/wiki/HTTP/2.0
     *
     * @var string
     */
    public const PROTOCOL_HTTP_2_0 = 'HTTP/2.0';
    /**
     * @see https://en.wikipedia.org/wiki/HTTP/2
     *
     * @var string
     */
    public const PROTOCOL_HTTP_2 = 'HTTP/2';
    /**
     * @see https://en.wikipedia.org/wiki/HTTP/3
     *
     * @var string
     */
    public const PROTOCOL_HTTP_3 = 'HTTP/3';
    // General headers ---------------------------------------------------------
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
     * @deprecated
     *
     * @var string
     */
    public const HEADER_CACHE_CONTROL = 'Cache-Control';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Connection
     * @deprecated
     *
     * @var string
     */
    public const HEADER_CONNECTION = 'Connection';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
     * @deprecated
     *
     * @var string
     */
    public const HEADER_CONTENT_DISPOSITION = 'Content-Disposition';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Date
     * @deprecated
     *
     * @var string
     */
    public const HEADER_DATE = 'Date';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Keep-Alive
     * @deprecated
     *
     * @var string
     */
    public const HEADER_KEEP_ALIVE = 'Keep-Alive';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Pragma
     * @deprecated
     *
     * @var string
     */
    public const HEADER_PRAGMA = 'Pragma';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Via
     * @deprecated
     *
     * @var string
     */
    public const HEADER_VIA = 'Via';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Warning
     * @deprecated
     *
     * @var string
     */
    public const HEADER_WARNING = 'Warning';
    // Representation headers --------------------------------------------------
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Encoding
     * @deprecated
     *
     * @var string
     */
    public const HEADER_CONTENT_ENCODING = 'Content-Encoding';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Language
     * @deprecated
     *
     * @var string
     */
    public const HEADER_CONTENT_LANGUAGE = 'Content-Language';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Location
     * @deprecated
     *
     * @var string
     */
    public const HEADER_CONTENT_LOCATION = 'Content-Location';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
     * @deprecated
     *
     * @var string
     */
    public const HEADER_CONTENT_TYPE = 'Content-Type';
    // Payload headers ---------------------------------------------------------
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Length
     * @deprecated
     *
     * @var string
     */
    public const HEADER_CONTENT_LENGTH = 'Content-Length';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Range
     * @deprecated
     *
     * @var string
     */
    public const HEADER_CONTENT_RANGE = 'Content-Range';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Link
     * @deprecated
     *
     * @var string
     */
    public const HEADER_LINK = 'Link';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Trailer
     * @deprecated
     *
     * @var string
     */
    public const HEADER_TRAILER = 'Trailer';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Transfer-Encoding
     * @deprecated
     *
     * @var string
     */
    public const HEADER_TRANSFER_ENCODING = 'Transfer-Encoding';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Upgrade
     * @deprecated
     *
     * @var string
     */
    public const HEADER_UPGRADE = 'Upgrade';
    // Custom headers ----------------------------------------------------------
    /**
     * @see https://riptutorial.com/http-headers/topic/10581/x-request-id
     * @deprecated
     *
     * @var string
     */
    public const HEADER_X_REQUEST_ID = 'X-Request-ID';

    public function getProtocol() : string;

    public function getStartLine() : string;

    public function getHeader(string $name) : ?string;

    public function hasHeader(string $name, string $value = null) : bool;

    /**
     * @return array<string,string>
     */
    public function getHeaders() : array;

    public function getBody() : string;
}
