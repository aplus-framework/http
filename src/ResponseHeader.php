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
class ResponseHeader extends Header
{
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Ranges
     *
     * @var string
     */
    public const string ACCEPT_RANGES = 'Accept-Ranges';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials
     *
     * @var string
     */
    public const string ACCESS_CONTROL_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Headers
     *
     * @var string
     */
    public const string ACCESS_CONTROL_ALLOW_HEADERS = 'Access-Control-Allow-Headers';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Methods
     *
     * @var string
     */
    public const string ACCESS_CONTROL_ALLOW_METHODS = 'Access-Control-Allow-Methods';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin
     *
     * @var string
     */
    public const string ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Expose-Headers
     *
     * @var string
     */
    public const string ACCESS_CONTROL_EXPOSE_HEADERS = 'Access-Control-Expose-Headers';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Max-Age
     *
     * @var string
     */
    public const string ACCESS_CONTROL_MAX_AGE = 'Access-Control-Max-Age';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Age
     *
     * @var string
     */
    public const string AGE = 'Age';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Allow
     *
     * @var string
     */
    public const string ALLOW = 'Allow';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Clear-Site-Data
     *
     * @var string
     */
    public const string CLEAR_SITE_DATA = 'Clear-Site-Data';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy
     *
     * @var string
     */
    public const string CONTENT_SECURITY_POLICY = 'Content-Security-Policy';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy-Report-Only
     *
     * @var string
     */
    public const string CONTENT_SECURITY_POLICY_REPORT_ONLY = 'Content-Security-Policy-Report-Only';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag
     *
     * @var string
     */
    public const string ETAG = 'ETag';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expect-CT
     *
     * @var string
     */
    public const string EXPECT_CT = 'Expect-CT';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expires
     *
     * @var string
     */
    public const string EXPIRES = 'Expires';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy
     *
     * @var string
     */
    public const string FEATURE_POLICY = 'Feature-Policy';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Last-Modified
     *
     * @var string
     */
    public const string LAST_MODIFIED = 'Last-Modified';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Location
     *
     * @var string
     */
    public const string LOCATION = 'Location';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Proxy-Authenticate
     *
     * @var string
     */
    public const string PROXY_AUTHENTICATE = 'Proxy-Authenticate';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Public-Key-Pins
     *
     * @var string
     */
    public const string PUBLIC_KEY_PINS = 'Public-Key-Pins';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Public-Key-Pins-Report-Only
     *
     * @var string
     */
    public const string PUBLIC_KEY_PINS_REPORT_ONLY = 'Public-Key-Pins-Report-Only';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy
     *
     * @var string
     */
    public const string REFERRER_POLICY = 'Referrer-Policy';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Retry-After
     *
     * @var string
     */
    public const string RETRY_AFTER = 'Retry-After';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Server
     *
     * @var string
     */
    public const string SERVER = 'Server';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie
     *
     * @var string
     */
    public const string SET_COOKIE = 'Set-Cookie';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/SourceMap
     *
     * @var string
     */
    public const string SOURCEMAP = 'SourceMap';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security
     *
     * @var string
     */
    public const string STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Timing-Allow-Origin
     *
     * @var string
     */
    public const string TIMING_ALLOW_ORIGIN = 'Timing-Allow-Origin';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Tk
     *
     * @var string
     */
    public const string TK = 'Tk';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Vary
     *
     * @var string
     */
    public const string VARY = 'Vary';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/WWW-Authenticate
     *
     * @var string
     */
    public const string WWW_AUTHENTICATE = 'WWW-Authenticate';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options
     *
     * @var string
     */
    public const string X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-DNS-Prefetch-Control
     *
     * @var string
     */
    public const string X_DNS_PREFETCH_CONTROL = 'X-DNS-Prefetch-Control';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options
     *
     * @var string
     */
    public const string X_FRAME_OPTIONS = 'X-Frame-Options';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-XSS-Protection
     *
     * @var string
     */
    public const string X_XSS_PROTECTION = 'X-XSS-Protection';
    // -------------------------------------------------------------------------
    // Custom
    // -------------------------------------------------------------------------
    /**
     * @var string
     */
    public const string X_POWERED_BY = 'X-Powered-By';
}
