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

/**
 * Trait HeaderTrait.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
 *
 * @package http
 */
trait HeaderTrait
{
    // -------------------------------------------------------------------------
    // General headers (Request and Response)
    // -------------------------------------------------------------------------
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
     */
    public const string CACHE_CONTROL = 'Cache-Control';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Connection
     */
    public const string CONNECTION = 'Connection';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
     */
    public const string CONTENT_DISPOSITION = 'Content-Disposition';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Date
     */
    public const string DATE = 'Date';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Keep-Alive
     */
    public const string KEEP_ALIVE = 'Keep-Alive';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Pragma
     */
    public const string PRAGMA = 'Pragma';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Via
     */
    public const string VIA = 'Via';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Warning
     */
    public const string WARNING = 'Warning';
    // -------------------------------------------------------------------------
    // Representation headers (Request and Response)
    // -------------------------------------------------------------------------
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Encoding
     */
    public const string CONTENT_ENCODING = 'Content-Encoding';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Language
     */
    public const string CONTENT_LANGUAGE = 'Content-Language';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Location
     */
    public const string CONTENT_LOCATION = 'Content-Location';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
     */
    public const string CONTENT_TYPE = 'Content-Type';
    // -------------------------------------------------------------------------
    // Payload headers (Request and Response)
    // -------------------------------------------------------------------------
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Length
     */
    public const string CONTENT_LENGTH = 'Content-Length';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Range
     */
    public const string CONTENT_RANGE = 'Content-Range';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Link
     */
    public const string LINK = 'Link';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Trailer
     */
    public const string TRAILER = 'Trailer';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Transfer-Encoding
     */
    public const string TRANSFER_ENCODING = 'Transfer-Encoding';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Upgrade
     */
    public const string UPGRADE = 'Upgrade';
    // -------------------------------------------------------------------------
    // Custom
    // -------------------------------------------------------------------------
    /**
     * @see https://riptutorial.com/http-headers/topic/10581/x-request-id
     */
    public const string X_REQUEST_ID = 'X-Request-ID';
    /**
     * Header names.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
     *
     * @var array<string,string>
     */
    protected static array $headers = [
        // ---------------------------------------------------------------------
        // General headers (Request and Response)
        // ---------------------------------------------------------------------
        'cache-control' => 'Cache-Control',
        'connection' => 'Connection',
        'content-disposition' => 'Content-Disposition',
        'date' => 'Date',
        'keep-alive' => 'Keep-Alive',
        'link' => 'Link',
        'pragma' => 'Pragma',
        'via' => 'Via',
        'warning' => 'Warning',
        // ---------------------------------------------------------------------
        // Representation headers (Request and Response)
        // ---------------------------------------------------------------------
        'content-encoding' => 'Content-Encoding',
        'content-language' => 'Content-Language',
        'content-location' => 'Content-Location',
        'content-type' => 'Content-Type',
        // ---------------------------------------------------------------------
        // Payload headers (Request and Response)
        // ---------------------------------------------------------------------
        'content-length' => 'Content-Length',
        'content-range' => 'Content-Range',
        'trailer' => 'Trailer',
        'transfer-encoding' => 'Transfer-Encoding',
        // ---------------------------------------------------------------------
        // Request headers
        // ---------------------------------------------------------------------
        'accept' => 'Accept',
        'accept-charset' => 'Accept-Charset',
        'accept-encoding' => 'Accept-Encoding',
        'accept-language' => 'Accept-Language',
        'access-control-request-headers' => 'Access-Control-Request-Headers',
        'access-control-request-method' => 'Access-Control-Request-Method',
        'authorization' => 'Authorization',
        'cdn-loop' => 'CDN-Loop',
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
        'priority' => 'Priority',
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
        'x-real-ip' => 'X-Real-IP',
        'x-requested-with' => 'X-Requested-With',
        // ---------------------------------------------------------------------
        // Response headers
        // ---------------------------------------------------------------------
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
        'x-robots-tag' => 'X-Robots-Tag',
        'x-xss-protection' => 'X-XSS-Protection',
        // ---------------------------------------------------------------------
        // Custom (Response)
        // ---------------------------------------------------------------------
        'x-request-id' => 'X-Request-ID',
        'x-powered-by' => 'X-Powered-By',
        // ---------------------------------------------------------------------
        // WebSocket
        // ---------------------------------------------------------------------
        'sec-websocket-extensions' => 'Sec-WebSocket-Extensions',
        'sec-websocket-key' => 'Sec-WebSocket-Key',
        'sec-websocket-protocol' => 'Sec-WebSocket-Protocol',
        'sec-websocket-version' => 'Sec-WebSocket-Version',
    ];

    public static function getName(string $name) : string
    {
        return static::$headers[\strtolower($name)] ?? $name;
    }

    public static function setName(string $name) : void
    {
        static::$headers[\strtolower($name)] = $name;
    }

    /**
     * @return array<string>
     */
    public static function getMultilines() : array
    {
        return [
            'cdn-loop',
            'date',
            'expires',
            'if-modified-since',
            'if-range',
            'if-unmodified-since',
            'last-modified',
            'proxy-authenticate',
            'retry-after',
            'set-cookie',
            'x-robots-tag',
            'www-authenticate',
        ];
    }

    public static function isMultiline(string $name) : bool
    {
        return \in_array(\strtolower($name), static::getMultilines(), true);
    }
}
