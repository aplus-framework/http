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
 * Class RequestHeader.
 *
 * @see https://developer.mozilla.org/en-US/docs/Glossary/Request_header
 *
 * @package http
 */
class RequestHeader extends Header
{
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept
     *
     * @var string
     */
    public const ACCEPT = 'Accept';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Charset
     *
     * @var string
     */
    public const ACCEPT_CHARSET = 'Accept-Charset';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Encoding
     *
     * @var string
     */
    public const ACCEPT_ENCODING = 'Accept-Encoding';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
     *
     * @var string
     */
    public const ACCEPT_LANGUAGE = 'Accept-Language';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Headers
     *
     * @var string
     */
    public const ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Method
     *
     * @var string
     */
    public const ACCESS_CONTROL_REQUEST_METHOD = 'Access-Control-Request-Method';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Authorization
     *
     * @var string
     */
    public const AUTHORIZATION = 'Authorization';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cookie
     *
     * @var string
     */
    public const COOKIE = 'Cookie';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/DNT
     *
     * @var string
     */
    public const DNT = 'DNT';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expect
     *
     * @var string
     */
    public const EXPECT = 'Expect';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Forwarded
     *
     * @var string
     */
    public const FORWARDED = 'Forwarded';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/From
     *
     * @var string
     */
    public const FROM = 'From';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Host
     *
     * @var string
     */
    public const HOST = 'Host';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Match
     *
     * @var string
     */
    public const IF_MATCH = 'If-Match';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Modified-Since
     *
     * @var string
     */
    public const IF_MODIFIED_SINCE = 'If-Modified-Since';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-None-Match
     *
     * @var string
     */
    public const IF_NONE_MATCH = 'If-None-Match';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Range
     *
     * @var string
     */
    public const IF_RANGE = 'If-Range';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Unmodified-Since
     *
     * @var string
     */
    public const IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin
     *
     * @var string
     */
    public const ORIGIN = 'Origin';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Proxy-Authorization
     *
     * @var string
     */
    public const PROXY_AUTHORIZATION = 'Proxy-Authorization';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Range
     *
     * @var string
     */
    public const RANGE = 'Range';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referer
     *
     * @var string
     */
    public const REFERER = 'Referer';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Dest
     *
     * @var string
     */
    public const SEC_FETCH_DEST = 'Sec-Fetch-Dest';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode
     *
     * @var string
     */
    public const SEC_FETCH_MODE = 'Sec-Fetch-Mode';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site
     *
     * @var string
     */
    public const SEC_FETCH_SITE = 'Sec-Fetch-Site';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-User
     *
     * @var string
     */
    public const SEC_FETCH_USER = 'Sec-Fetch-User';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/TE
     *
     * @var string
     */
    public const TE = 'TE';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Upgrade-Insecure-Requests
     *
     * @var string
     */
    public const UPGRADE_INSECURE_REQUESTS = 'Upgrade-Insecure-Requests';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent
     *
     * @var string
     */
    public const USER_AGENT = 'User-Agent';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
     *
     * @var string
     */
    public const X_FORWARDED_FOR = 'X-Forwarded-For';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Host
     *
     * @var string
     */
    public const X_FORWARDED_HOST = 'X-Forwarded-Host';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Proto
     *
     * @var string
     */
    public const X_FORWARDED_PROTO = 'X-Forwarded-Proto';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Real-IP
     *
     * @var string
     */
    public const X_REAL_IP = 'X-Real-IP';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Requested-With
     *
     * @var string
     */
    public const X_REQUESTED_WITH = 'X-Requested-With';

    /**
     * @param array<string,scalar> $input
     *
     * @return array<string,string>
     */
    public static function parseInput(array $input) : array
    {
        $headers = [];
        foreach ($input as $name => $value) {
            if (\str_starts_with($name, 'HTTP_')) {
                $name = \strtr(\substr($name, 5), ['_' => '-']);
                $name = static::getName($name);
                $headers[$name] = (string) $value;
            }
        }
        return $headers;
    }
}
