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
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods
 *
 * @package http
 */
class Method
{
    /**
     * CONNECT request method.
     *
     * The CONNECT HTTP method requests that a proxy establish an HTTP tunnel to
     * a destination server, and if successful, blindly forward data in both
     * directions until the tunnel is closed.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods/CONNECT
     */
    public const string CONNECT = 'CONNECT';
    /**
     * DELETE request method.
     *
     * The DELETE HTTP method asks the server to delete a specified resource.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods/DELETE
     */
    public const string DELETE = 'DELETE';
    /**
     * GET request method.
     *
     * The GET HTTP method requests a representation of the specified resource.
     * Requests using GET should only be used to request data and shouldn't
     * contain a body.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods/GET
     */
    public const string GET = 'GET';
    /**
     * HEAD request method.
     *
     * The HEAD HTTP method requests the metadata of a resource in the form of
     * headers that the server would have sent if the GET method was used
     * instead.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods/HEAD
     */
    public const string HEAD = 'HEAD';
    /**
     * OPTIONS request method.
     *
     * The OPTIONS HTTP method requests permitted communication options for a
     * given URL or server. This can be used to test the allowed HTTP methods
     * for a request, or to determine whether a request would succeed when
     * making a CORS preflighted request.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods/OPTIONS
     */
    public const string OPTIONS = 'OPTIONS';
    /**
     * PATCH request method.
     *
     * The PATCH HTTP method applies partial modifications to a resource.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods/PATCH
     */
    public const string PATCH = 'PATCH';
    /**
     * POST request method.
     *
     * The POST HTTP method sends data to the server. The type of the body of
     * the request is indicated by the Content-Type header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods/POST
     * @see HeaderTrait::CONTENT_TYPE
     */
    public const string POST = 'POST';
    /**
     * PUT request method.
     *
     * The PUT HTTP method creates a new resource or replaces a representation
     * of the target resource with the request content.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods/PUT
     */
    public const string PUT = 'PUT';
    /**
     * QUERY request method.
     *
     * The QUERY HTTP method initiates a server-side query. It requests that the
     * target resource process the request content in a safe and idempotent
     * manner, returning the result in the response.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods/QUERY
     * @see HeaderTrait::CONTENT_TYPE
     */
    public const string QUERY = 'QUERY';
    /**
     * TRACE request method.
     *
     * The TRACE HTTP method performs a message loop-back test along the path
     * to the target resource.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Methods/TRACE
     */
    public const string TRACE = 'TRACE';
    /**
     * @var array<int,string>
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
        'QUERY',
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
