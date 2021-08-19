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
 * @package http
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP
 */
interface MessageInterface extends Stringable
{
    public const PROTOCOL_HTTP_1_0 = 'HTTP/1.0';
    public const PROTOCOL_HTTP_1_1 = 'HTTP/1.1';
    public const PROTOCOL_HTTP_2_0 = 'HTTP/2.0';
    public const PROTOCOL_HTTP_2 = 'HTTP/2';
    public const PROTOCOL_HTTP_3 = 'HTTP/3';
    // General headers ---------------------------------------------------------
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
     */
    public const HEADER_CACHE_CONTROL = 'Cache-Control';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Connection
     */
    public const HEADER_CONNECTION = 'Connection';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
     */
    public const HEADER_CONTENT_DISPOSITION = 'Content-Disposition';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Date
     */
    public const HEADER_DATE = 'Date';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Keep-Alive
     */
    public const HEADER_KEEP_ALIVE = 'Keep-Alive';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Pragma
     */
    public const HEADER_PRAGMA = 'Pragma';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Via
     */
    public const HEADER_VIA = 'Via';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Warning
     */
    public const HEADER_WARNING = 'Warning';
    // Representation headers --------------------------------------------------
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Encoding
     */
    public const HEADER_CONTENT_ENCODING = 'Content-Encoding';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Language
     */
    public const HEADER_CONTENT_LANGUAGE = 'Content-Language';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Location
     */
    public const HEADER_CONTENT_LOCATION = 'Content-Location';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
     */
    public const HEADER_CONTENT_TYPE = 'Content-Type';
    // Payload headers ---------------------------------------------------------
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Length
     */
    public const HEADER_CONTENT_LENGTH = 'Content-Length';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Range
     */
    public const HEADER_CONTENT_RANGE = 'Content-Range';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Link
     */
    public const HEADER_LINK = 'Link';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Trailer
     */
    public const HEADER_TRAILER = 'Trailer';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Transfer-Encoding
     */
    public const HEADER_TRANSFER_ENCODING = 'Transfer-Encoding';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Upgrade
     */
    public const HEADER_UPGRADE = 'Upgrade';
    // Custom headers ----------------------------------------------------------
    /**
     * @see https://riptutorial.com/http-headers/topic/10581/x-request-id
     */
    public const HEADER_X_REQUEST_ID = 'X-Request-ID';

    public function getProtocol() : string;

    public function getStartLine() : string;

    public function getHeader(string $name) : ?string;

    /**
     * @return array<string,string>
     */
    public function getHeaders() : array;

    public function getBody() : string;
}
