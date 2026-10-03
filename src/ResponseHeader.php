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
 * Class ResponseHeader.
 *
 * @see https://developer.mozilla.org/en-US/docs/Glossary/Response_header
 *
 * @package http
 */
class ResponseHeader
{
    use HeaderTrait;

    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Accept-Ranges
     */
    public const string ACCEPT_RANGES = 'Accept-Ranges';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Access-Control-Allow-Credentials
     */
    public const string ACCESS_CONTROL_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Access-Control-Allow-Headers
     */
    public const string ACCESS_CONTROL_ALLOW_HEADERS = 'Access-Control-Allow-Headers';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Access-Control-Allow-Methods
     */
    public const string ACCESS_CONTROL_ALLOW_METHODS = 'Access-Control-Allow-Methods';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Access-Control-Allow-Origin
     */
    public const string ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Access-Control-Expose-Headers
     */
    public const string ACCESS_CONTROL_EXPOSE_HEADERS = 'Access-Control-Expose-Headers';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Access-Control-Max-Age
     */
    public const string ACCESS_CONTROL_MAX_AGE = 'Access-Control-Max-Age';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Age
     */
    public const string AGE = 'Age';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Allow
     */
    public const string ALLOW = 'Allow';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Clear-Site-Data
     */
    public const string CLEAR_SITE_DATA = 'Clear-Site-Data';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Content-Security-Policy
     */
    public const string CONTENT_SECURITY_POLICY = 'Content-Security-Policy';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Content-Security-Policy-Report-Only
     */
    public const string CONTENT_SECURITY_POLICY_REPORT_ONLY = 'Content-Security-Policy-Report-Only';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/ETag
     */
    public const string ETAG = 'ETag';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Expect-CT
     */
    public const string EXPECT_CT = 'Expect-CT';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Expires
     */
    public const string EXPIRES = 'Expires';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Feature-Policy
     */
    public const string FEATURE_POLICY = 'Feature-Policy';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Last-Modified
     */
    public const string LAST_MODIFIED = 'Last-Modified';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Location
     */
    public const string LOCATION = 'Location';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Proxy-Authenticate
     */
    public const string PROXY_AUTHENTICATE = 'Proxy-Authenticate';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Public-Key-Pins
     */
    public const string PUBLIC_KEY_PINS = 'Public-Key-Pins';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Public-Key-Pins-Report-Only
     */
    public const string PUBLIC_KEY_PINS_REPORT_ONLY = 'Public-Key-Pins-Report-Only';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Referrer-Policy
     */
    public const string REFERRER_POLICY = 'Referrer-Policy';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Retry-After
     */
    public const string RETRY_AFTER = 'Retry-After';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Server
     */
    public const string SERVER = 'Server';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Set-Cookie
     */
    public const string SET_COOKIE = 'Set-Cookie';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/SourceMap
     */
    public const string SOURCEMAP = 'SourceMap';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Strict-Transport-Security
     */
    public const string STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Timing-Allow-Origin
     */
    public const string TIMING_ALLOW_ORIGIN = 'Timing-Allow-Origin';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Tk
     */
    public const string TK = 'Tk';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Vary
     */
    public const string VARY = 'Vary';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/WWW-Authenticate
     */
    public const string WWW_AUTHENTICATE = 'WWW-Authenticate';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Content-Type-Options
     */
    public const string X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-DNS-Prefetch-Control
     */
    public const string X_DNS_PREFETCH_CONTROL = 'X-DNS-Prefetch-Control';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Frame-Options
     */
    public const string X_FRAME_OPTIONS = 'X-Frame-Options';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Robots-Tag
     * @see https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag
     */
    public const string X_ROBOTS_TAG = 'X-Robots-Tag';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-XSS-Protection
     */
    public const string X_XSS_PROTECTION = 'X-XSS-Protection';
    // -------------------------------------------------------------------------
    // Custom
    // -------------------------------------------------------------------------
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Powered-By
     */
    public const string X_POWERED_BY = 'X-Powered-By';
}
