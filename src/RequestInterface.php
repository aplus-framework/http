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
     * @deprecated
     *
     * @var string
     */
    public const HEADER_ACCEPT = 'Accept';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Charset
     * @deprecated
     *
     * @var string
     */
    public const HEADER_ACCEPT_CHARSET = 'Accept-Charset';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Encoding
     * @deprecated
     *
     * @var string
     */
    public const HEADER_ACCEPT_ENCODING = 'Accept-Encoding';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
     * @deprecated
     *
     * @var string
     */
    public const HEADER_ACCEPT_LANGUAGE = 'Accept-Language';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Headers
     * @deprecated
     *
     * @var string
     */
    public const HEADER_ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Request-Method
     * @deprecated
     *
     * @var string
     */
    public const HEADER_ACCESS_CONTROL_REQUEST_METHOD = 'Access-Control-Request-Method';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Authorization
     * @deprecated
     *
     * @var string
     */
    public const HEADER_AUTHORIZATION = 'Authorization';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cookie
     * @deprecated
     *
     * @var string
     */
    public const HEADER_COOKIE = 'Cookie';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/DNT
     * @deprecated
     *
     * @var string
     */
    public const HEADER_DNT = 'DNT';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Expect
     * @deprecated
     *
     * @var string
     */
    public const HEADER_EXPECT = 'Expect';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Forwarded
     * @deprecated
     *
     * @var string
     */
    public const HEADER_FORWARDED = 'Forwarded';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/From
     * @deprecated
     *
     * @var string
     */
    public const HEADER_FROM = 'From';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Host
     * @deprecated
     *
     * @var string
     */
    public const HEADER_HOST = 'Host';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Match
     * @deprecated
     *
     * @var string
     */
    public const HEADER_IF_MATCH = 'If-Match';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Modified-Since
     * @deprecated
     *
     * @var string
     */
    public const HEADER_IF_MODIFIED_SINCE = 'If-Modified-Since';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-None-Match
     * @deprecated
     *
     * @var string
     */
    public const HEADER_IF_NONE_MATCH = 'If-None-Match';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Range
     * @deprecated
     *
     * @var string
     */
    public const HEADER_IF_RANGE = 'If-Range';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Unmodified-Since
     * @deprecated
     *
     * @var string
     */
    public const HEADER_IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin
     * @deprecated
     *
     * @var string
     */
    public const HEADER_ORIGIN = 'Origin';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Proxy-Authorization
     * @deprecated
     *
     * @var string
     */
    public const HEADER_PROXY_AUTHORIZATION = 'Proxy-Authorization';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Range
     * @deprecated
     *
     * @var string
     */
    public const HEADER_RANGE = 'Range';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referer
     * @deprecated
     *
     * @var string
     */
    public const HEADER_REFERER = 'Referer';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Dest
     * @deprecated
     *
     * @var string
     */
    public const HEADER_SEC_FETCH_DEST = 'Sec-Fetch-Dest';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode
     * @deprecated
     *
     * @var string
     */
    public const HEADER_SEC_FETCH_MODE = 'Sec-Fetch-Mode';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site
     * @deprecated
     *
     * @var string
     */
    public const HEADER_SEC_FETCH_SITE = 'Sec-Fetch-Site';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-User
     * @deprecated
     *
     * @var string
     */
    public const HEADER_SEC_FETCH_USER = 'Sec-Fetch-User';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/TE
     * @deprecated
     *
     * @var string
     */
    public const HEADER_TE = 'TE';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Upgrade-Insecure-Requests
     * @deprecated
     *
     * @var string
     */
    public const HEADER_UPGRADE_INSECURE_REQUESTS = 'Upgrade-Insecure-Requests';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent
     * @deprecated
     *
     * @var string
     */
    public const HEADER_USER_AGENT = 'User-Agent';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
     * @deprecated
     *
     * @var string
     */
    public const HEADER_X_FORWARDED_FOR = 'X-Forwarded-For';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Host
     * @deprecated
     *
     * @var string
     */
    public const HEADER_X_FORWARDED_HOST = 'X-Forwarded-Host';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-Proto
     * @deprecated
     *
     * @var string
     */
    public const HEADER_X_FORWARDED_PROTO = 'X-Forwarded-Proto';
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Requested-With
     * @deprecated
     *
     * @var string
     */
    public const HEADER_X_REQUESTED_WITH = 'X-Requested-With';
    /**
     * The HTTP CONNECT method starts two-way communications with the requested
     * resource. It can be used to open a tunnel.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/CONNECT
     * @deprecated
     *
     * @var string
     */
    public const METHOD_CONNECT = 'CONNECT';
    /**
     * The HTTP DELETE request method deletes the specified resource.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/DELETE
     * @deprecated
     *
     * @var string
     */
    public const METHOD_DELETE = 'DELETE';
    /**
     * The HTTP GET method requests a representation of the specified resource.
     * Requests using GET should only be used to request data (they shouldn't
     * include data).
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/GET
     * @deprecated
     *
     * @var string
     */
    public const METHOD_GET = 'GET';
    /**
     * The HTTP HEAD method requests the headers that would be returned if the
     * HEAD request's URL was instead requested with the HTTP GET method.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
     * @deprecated
     *
     * @var string
     */
    public const METHOD_HEAD = 'HEAD';
    /**
     * The HTTP OPTIONS method requests permitted communication options for a
     * given URL or server. A client can specify a URL with this method, or an
     * asterisk (*) to refer to the entire server.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/OPTIONS
     * @deprecated
     *
     * @var string
     */
    public const METHOD_OPTIONS = 'OPTIONS';
    /**
     * The HTTP PATCH request method applies partial modifications to a resource.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PATCH
     * @deprecated
     *
     * @var string
     */
    public const METHOD_PATCH = 'PATCH';
    /**
     * The HTTP POST method sends data to the server. The type of the body of
     * the request is indicated by the Content-Type header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/POST
     * @see MessageInterface::HEADER_CONTENT_TYPE
     * @deprecated
     *
     * @var string
     */
    public const METHOD_POST = 'POST';
    /**
     * The HTTP PUT request method creates a new resource or replaces a
     * representation of the target resource with the request payload.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PUT
     * @deprecated
     *
     * @var string
     */
    public const METHOD_PUT = 'PUT';
    /**
     * The HTTP TRACE method performs a message loop-back test along the path to
     * the target resource, providing a useful debugging mechanism.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/TRACE
     * @deprecated
     *
     * @var string
     */
    public const METHOD_TRACE = 'TRACE';

    public function getMethod() : string;

    public function hasMethod(string $method) : bool;

    public function getUrl() : URL;
}
