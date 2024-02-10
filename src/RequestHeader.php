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
     */
    public const string ACCEPT = 'Accept';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Charset
     */
    public const string ACCEPT_CHARSET = 'Accept-Charset';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Encoding
     */
    public const string ACCEPT_ENCODING = 'Accept-Encoding';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
     */
    public const string ACCEPT_LANGUAGE = 'Accept-Language';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Headers
     */
    public const string ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Method
     */
    public const string ACCESS_CONTROL_REQUEST_METHOD = 'Access-Control-Request-Method';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Authorization
     */
    public const string AUTHORIZATION = 'Authorization';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cookie
     */
    public const string COOKIE = 'Cookie';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/DNT
     */
    public const string DNT = 'DNT';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expect
     */
    public const string EXPECT = 'Expect';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Forwarded
     */
    public const string FORWARDED = 'Forwarded';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/From
     */
    public const string FROM = 'From';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Host
     */
    public const string HOST = 'Host';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Match
     */
    public const string IF_MATCH = 'If-Match';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Modified-Since
     */
    public const string IF_MODIFIED_SINCE = 'If-Modified-Since';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-None-Match
     */
    public const string IF_NONE_MATCH = 'If-None-Match';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Range
     */
    public const string IF_RANGE = 'If-Range';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Unmodified-Since
     */
    public const string IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin
     */
    public const string ORIGIN = 'Origin';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Proxy-Authorization
     */
    public const string PROXY_AUTHORIZATION = 'Proxy-Authorization';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Range
     */
    public const string RANGE = 'Range';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referer
     */
    public const string REFERER = 'Referer';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Dest
     */
    public const string SEC_FETCH_DEST = 'Sec-Fetch-Dest';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode
     */
    public const string SEC_FETCH_MODE = 'Sec-Fetch-Mode';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site
     */
    public const string SEC_FETCH_SITE = 'Sec-Fetch-Site';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-User
     */
    public const string SEC_FETCH_USER = 'Sec-Fetch-User';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/TE
     */
    public const string TE = 'TE';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Upgrade-Insecure-Requests
     */
    public const string UPGRADE_INSECURE_REQUESTS = 'Upgrade-Insecure-Requests';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent
     */
    public const string USER_AGENT = 'User-Agent';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
     */
    public const string X_FORWARDED_FOR = 'X-Forwarded-For';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Host
     */
    public const string X_FORWARDED_HOST = 'X-Forwarded-Host';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Proto
     */
    public const string X_FORWARDED_PROTO = 'X-Forwarded-Proto';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Real-IP
     */
    public const string X_REAL_IP = 'X-Real-IP';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Requested-With
     */
    public const string X_REQUESTED_WITH = 'X-Requested-With';

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
