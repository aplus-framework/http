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

use InvalidArgumentException;

/**
 * Class Method.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods
 *
 * @package http
 */
class Method
{
    /**
     * The HTTP CONNECT method starts two-way communications with the requested
     * resource. It can be used to open a tunnel.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/CONNECT
     *
     * @var string
     */
    public const CONNECT = 'CONNECT';
    /**
     * The HTTP DELETE request method deletes the specified resource.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/DELETE
     *
     * @var string
     */
    public const DELETE = 'DELETE';
    /**
     * The HTTP GET method requests a representation of the specified resource.
     * Requests using GET should only be used to request data (they shouldn't
     * include data).
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/GET
     *
     * @var string
     */
    public const GET = 'GET';
    /**
     * The HTTP HEAD method requests the headers that would be returned if the
     * HEAD request's URL was instead requested with the HTTP GET method.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
     *
     * @var string
     */
    public const HEAD = 'HEAD';
    /**
     * The HTTP OPTIONS method requests permitted communication options for a
     * given URL or server. A client can specify a URL with this method, or an
     * asterisk (*) to refer to the entire server.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/OPTIONS
     *
     * @var string
     */
    public const OPTIONS = 'OPTIONS';
    /**
     * The HTTP PATCH request method applies partial modifications to a resource.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PATCH
     *
     * @var string
     */
    public const PATCH = 'PATCH';
    /**
     * The HTTP POST method sends data to the server. The type of the body of
     * the request is indicated by the Content-Type header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/POST
     * @see Header::CONTENT_TYPE
     *
     * @var string
     */
    public const POST = 'POST';
    /**
     * The HTTP PUT request method creates a new resource or replaces a
     * representation of the target resource with the request payload.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PUT
     *
     * @var string
     */
    public const PUT = 'PUT';
    /**
     * The HTTP TRACE method performs a message loop-back test along the path to
     * the target resource, providing a useful debugging mechanism.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/TRACE
     *
     * @var string
     */
    public const TRACE = 'TRACE';
    /**
     * @var array<string>
     */
    protected static array $methods = [
        'CONNECT',
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
        'TRACE',
    ];

    /**
     * @param string $method
     *
     * @throws InvalidArgumentException for invalid method
     *
     * @return string
     */
    public static function validate(string $method) : string
    {
        $valid = \strtoupper($method);
        if (\in_array($valid, static::$methods, true)) {
            return $valid;
        }
        throw new InvalidArgumentException('Invalid request method: ' . $method);
    }
}
