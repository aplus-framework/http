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
 * Interface RequestInterface.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#http_requests
 *
 * @package http
 */
interface RequestInterface extends MessageInterface
{
    // Request headers ---------------------------------------------------------
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept
     *
     * @var string
     */
    public const HEADER_ACCEPT = 'Accept';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Charset
     *
     * @var string
     */
    public const HEADER_ACCEPT_CHARSET = 'Accept-Charset';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Encoding
     *
     * @var string
     */
    public const HEADER_ACCEPT_ENCODING = 'Accept-Encoding';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
     *
     * @var string
     */
    public const HEADER_ACCEPT_LANGUAGE = 'Accept-Language';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Headers
     *
     * @var string
     */
    public const HEADER_ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Method
     *
     * @var string
     */
    public const HEADER_ACCESS_CONTROL_REQUEST_METHOD = 'Access-Control-Request-Method';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Authorization
     *
     * @var string
     */
    public const HEADER_AUTHORIZATION = 'Authorization';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cookie
     *
     * @var string
     */
    public const HEADER_COOKIE = 'Cookie';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/DNT
     *
     * @var string
     */
    public const HEADER_DNT = 'DNT';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expect
     *
     * @var string
     */
    public const HEADER_EXPECT = 'Expect';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Forwarded
     *
     * @var string
     */
    public const HEADER_FORWARDED = 'Forwarded';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/From
     *
     * @var string
     */
    public const HEADER_FROM = 'From';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Host
     *
     * @var string
     */
    public const HEADER_HOST = 'Host';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Match
     *
     * @var string
     */
    public const HEADER_IF_MATCH = 'If-Match';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Modified-Since
     *
     * @var string
     */
    public const HEADER_IF_MODIFIED_SINCE = 'If-Modified-Since';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-None-Match
     *
     * @var string
     */
    public const HEADER_IF_NONE_MATCH = 'If-None-Match';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Range
     *
     * @var string
     */
    public const HEADER_IF_RANGE = 'If-Range';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Unmodified-Since
     *
     * @var string
     */
    public const HEADER_IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin
     *
     * @var string
     */
    public const HEADER_ORIGIN = 'Origin';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Proxy-Authorization
     *
     * @var string
     */
    public const HEADER_PROXY_AUTHORIZATION = 'Proxy-Authorization';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Range
     *
     * @var string
     */
    public const HEADER_RANGE = 'Range';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referer
     *
     * @var string
     */
    public const HEADER_REFERER = 'Referer';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/TE
     *
     * @var string
     */
    public const HEADER_TE = 'TE';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Upgrade-Insecure-Requests
     *
     * @var string
     */
    public const HEADER_UPGRADE_INSECURE_REQUESTS = 'Upgrade-Insecure-Requests';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent
     *
     * @var string
     */
    public const HEADER_USER_AGENT = 'User-Agent';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
     *
     * @var string
     */
    public const HEADER_X_FORWARDED_FOR = 'X-Forwarded-For';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Host
     *
     * @var string
     */
    public const HEADER_X_FORWARDED_HOST = 'X-Forwarded-Host';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Proto
     *
     * @var string
     */
    public const HEADER_X_FORWARDED_PROTO = 'X-Forwarded-Proto';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Requested-With
     *
     * @var string
     */
    public const HEADER_X_REQUESTED_WITH = 'X-Requested-With';

    public function getMethod() : string;

    public function getUrl() : URL;
}
