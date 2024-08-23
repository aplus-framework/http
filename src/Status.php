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
use LogicException;

/**
 * Class Status.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
 *
 * @package http
 */
class Status
{
    // -------------------------------------------------------------------------
    // Informational responses
    // -------------------------------------------------------------------------
    /**
     * 100 Continue.
     *
     * This interim response indicates that everything so far is OK and that the
     * client should continue the request, or ignore the response if the request
     * is already finished.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/100
     */
    public const int CONTINUE = 100;
    /**
     * 101 Switching Protocols.
     *
     * This code is sent in response to an Upgrade request header from the
     * client, and indicates the protocol the server is switching to.
     *
     * @see Header::UPGRADE
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/101
     */
    public const int SWITCHING_PROTOCOLS = 101;
    /**
     * 102 Processing (WebDAV).
     *
     * This code indicates that the server has received and is processing the
     * request, but no response is available yet.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/102
     */
    public const int PROCESSING = 102;
    /**
     * 103 Early Hints.
     *
     * This status code is primarily intended to be used with the Link header,
     * letting the user agent start preloading resources while the server
     * prepares a response.
     *
     * @see Header::LINK
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/103
     */
    public const int EARLY_HINTS = 103;
    // -------------------------------------------------------------------------
    // Successful responses
    // -------------------------------------------------------------------------
    /**
     * 200 OK.
     *
     * The request has succeeded. The meaning of the success depends on the
     * HTTP method:
     *
     * - GET: The resource has been fetched and is transmitted in the message
     * body.
     *
     * - HEAD: The representation headers are included in the response without
     * any message body.
     *
     * - PUT or POST: The resource describing the result of the action is
     * transmitted in the message body.
     *
     * - TRACE: The message body contains the request message as received by the
     * server.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/200
     */
    public const int OK = 200;
    /**
     * 201 Created.
     *
     * The request has succeeded and a new resource has been created as a
     * result. This is typically the response sent after POST requests, or some
     * PUT requests.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/201
     */
    public const int CREATED = 201;
    /**
     * 202 Accepted.
     *
     * The request has been received but not yet acted upon. It is noncommittal,
     * since there is no way in HTTP to later send an asynchronous response
     * indicating the outcome of the request. It is intended for cases where
     * another process or server handles the request, or for batch processing.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/202
     */
    public const int ACCEPTED = 202;
    /**
     * 203 Non-Authoritative Information.
     *
     * This response code means the returned meta-information is not exactly the
     * same as is available from the origin server, but is collected from a
     * local or a third-party copy. This is mostly used for mirrors or backups
     * of another resource. Except for that specific case, the "200 OK" response
     * is preferred to this status.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/203
     */
    public const int NON_AUTHORITATIVE_INFORMATION = 203;
    /**
     * 204 No Content.
     *
     * There is no content to send for this request, but the headers may be
     * useful. The user-agent may update its cached headers for this resource
     * with the new ones.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/204
     */
    public const int NO_CONTENT = 204;
    /**
     * 205 Reset Content.
     *
     * Tells the user-agent to reset the document which sent this request.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/205
     */
    public const int RESET_CONTENT = 205;
    /**
     * 206 Partial Content.
     *
     * This response code is used when the Range header is sent from the client
     * to request only part of a resource.
     *
     * @see RequestHeader::RANGE
     * @see Header::CONTENT_RANGE
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/206
     */
    public const int PARTIAL_CONTENT = 206;
    /**
     * 207 Multi-Status (WebDAV).
     *
     * Conveys information about multiple resources, for situations where
     * multiple status codes might be appropriate.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/207
     */
    public const int MULTI_STATUS = 207;
    /**
     * 208 Already Reported (WebDAV).
     *
     * Used inside a `<dav:propstat>` response element to avoid repeatedly
     * enumerating the internal members of multiple bindings to the same
     * collection.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/208
     */
    public const int ALREADY_REPORTED = 208;
    /**
     * 226 IM Used (HTTP Delta encoding).
     *
     * The server has fulfilled a GET request for the resource, and the response
     * is a representation of the result of one or more instance-manipulations
     * applied to the current instance.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/226
     */
    public const int IM_USED = 226;
    // -------------------------------------------------------------------------
    // Redirection messages
    // -------------------------------------------------------------------------
    /**
     * 300 Multiple Choices.
     *
     * The request has more than one possible response. The user-agent or user
     * should choose one of them. (There is no standardized way of choosing one
     * of the responses, but HTML links to the possibilities are recommended so
     * the user can pick.)
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/300
     */
    public const int MULTIPLE_CHOICES = 300;
    /**
     * 301 Moved Permanently.
     *
     * The URL of the requested resource has been changed permanently. The new
     * URL is given in the response.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/301
     */
    public const int MOVED_PERMANENTLY = 301;
    /**
     * 302 Found.
     *
     * This response code means that the URI of requested resource has been
     * changed temporarily. Further changes in the URI might be made in the
     * future. Therefore, this same URI should be used by the client in future
     * requests.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/302
     */
    public const int FOUND = 302;
    /**
     * 303 See Other.
     *
     * The server sent this response to direct the client to get the requested
     * resource at another URI with a GET request.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303
     */
    public const int SEE_OTHER = 303;
    /**
     * 304 Not Modified.
     *
     * This is used for caching purposes. It tells the client that the response
     * has not been modified, so the client can continue to use the same cached
     * version of the response.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/304
     */
    public const int NOT_MODIFIED = 304;
    /**
     * 305 Use Proxy.
     *
     * Defined in a previous version of the HTTP specification to indicate that
     * a requested response must be accessed by a proxy. It has been deprecated
     * due to security concerns regarding in-band configuration of a proxy.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/305
     */
    public const int USE_PROXY = 305;
    /**
     * 306 unused.
     *
     * This response code is no longer used; it is just reserved. It was used in
     * a previous version of the HTTP/1.1 specification.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/306
     */
    public const int SWITCH_PROXY = 306;
    /**
     * 307 Temporary Redirect.
     *
     * The server sends this response to direct the client to get the requested
     * resource at another URI with same method that was used in the prior
     * request. This has the same semantics as the `302 Found` HTTP response
     * code, with the exception that the user agent must not change the HTTP
     * method used: If a `POST` was used in the first request, a `POST` must be
     * used in the second request.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/307
     */
    public const int TEMPORARY_REDIRECT = 307;
    /**
     * 308 Permanent Redirect.
     *
     * This means that the resource is now permanently located at another URI,
     * specified by the `Location:` HTTP Response header. This has the same
     * semantics as the `301 Moved Permanently` HTTP response code, with the
     * exception that the user agent must not change the HTTP method used: If a
     * `POST` was used in the first request, a `POST` must be used in the second
     * request.
     *
     * @see ResponseHeader::LOCATION
     * @see Status::MOVED_PERMANENTLY
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/308
     */
    public const int PERMANENT_REDIRECT = 308;
    // -------------------------------------------------------------------------
    // Client error responses
    // -------------------------------------------------------------------------
    /**
     * 400 Bad Request.
     *
     * The server could not understand the request due to invalid syntax.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/400
     */
    public const int BAD_REQUEST = 400;
    /**
     * 401 Unauthorized.
     *
     * Although the HTTP standard specifies "unauthorized", semantically this
     * response means "unauthenticated". That is, the client must authenticate
     * itself to get the requested response.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/401
     */
    public const int UNAUTHORIZED = 401;
    /**
     * 402 Payment Required.
     *
     * This response code is reserved for future use. The initial aim for
     * creating this code was using it for digital payment systems, however this
     * status code is used very rarely and no standard convention exists.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/402
     */
    public const int PAYMENT_REQUIRED = 402;
    /**
     * 403 Forbidden.
     *
     * The client does not have access rights to the content; that is, it is
     * unauthorized, so the server is refusing to give the requested resource.
     * Unlike 401, the client's identity is known to the server.
     *
     * @see Status::UNAUTHORIZED
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/403
     */
    public const int FORBIDDEN = 403;
    /**
     * 404 Not Found.
     *
     * The server can not find the requested resource. In the browser, this
     * means the URL is not recognized. In an API, this can also mean that the
     * endpoint is valid but the resource itself does not exist. Servers may
     * also send this response instead of 403 to hide the existence of a
     * resource from an unauthorized client. This response code is probably the
     * most famous one due to its frequent occurrence on the web.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/404
     */
    public const int NOT_FOUND = 404;
    /**
     * 405 Method Not Allowed.
     *
     * The request method is known by the server but is not supported by the
     * target resource. For example, an API may forbid DELETE-ing a resource.
     *
     * @see ResponseHeader::ALLOW
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/405
     */
    public const int METHOD_NOT_ALLOWED = 405;
    /**
     * 406 Not Acceptable.
     *
     * This response is sent when the web server, after performing server-driven
     * content negotiation, doesn't find any content that conforms to the
     * criteria given by the user agent.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/406
     */
    public const int NOT_ACCEPTABLE = 406;
    /**
     * 407 Proxy Authentication Required.
     *
     * This is similar to 401 but authentication is needed to be done by a
     * proxy.
     *
     * @see Status::UNAUTHORIZED
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/407
     */
    public const int PROXY_AUTHENTICATION_REQUIRED = 407;
    /**
     * 408 Request Timeout.
     *
     * This response is sent on an idle connection by some servers, even without
     * any previous request by the client. It means that the server would like
     * to shut down this unused connection. This response is used much more
     * since some browsers, like Chrome, Firefox 27+, or IE9, use HTTP
     * pre-connection mechanisms to speed up surfing. Also note that some
     * servers merely shut down the connection without sending this message.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/408
     */
    public const int REQUEST_TIMEOUT = 408;
    /**
     * 409 Conflict.
     *
     * This response is sent when a request conflicts with the current state of
     * the server.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/409
     */
    public const int CONFLICT = 409;
    /**
     * 410 Gone.
     *
     * This response is sent when the requested content has been permanently
     * deleted from server, with no forwarding address. Clients are expected to
     * remove their caches and links to the resource. The HTTP specification
     * intends this status code to be used for "limited-time, promotional
     * services". APIs should not feel compelled to indicate resources that have
     * been deleted with this status code.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/410
     */
    public const int GONE = 410;
    /**
     * 411 Length Required.
     *
     * Server rejected the request because the Content-Length header field is
     * not defined and the server requires it.
     *
     * @see Header::CONTENT_LENGTH
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/411
     */
    public const int LENGTH_REQUIRED = 411;
    /**
     * 412 Precondition Failed.
     *
     * The client has indicated preconditions in its headers which the server
     * does not meet.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/412
     */
    public const int PRECONDITION_FAILED = 412;
    /**
     * 413 Payload Too Large.
     *
     * Request entity is larger than limits defined by server; the server might
     * close the connection or return an `Retry-After` header field.
     *
     * @see ResponseHeader::RETRY_AFTER
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/413
     */
    public const int PAYLOAD_TOO_LARGE = 413;
    /**
     * 414 URI Too Long.
     *
     * The URI requested by the client is longer than the server is willing to
     * interpret.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/414
     */
    public const int URI_TOO_LARGE = 414;
    /**
     * 415 Unsupported Media Type.
     *
     * The media format of the requested data is not supported by the server,
     * so the server is rejecting the request.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/415
     */
    public const int UNSUPPORTED_MEDIA_TYPE = 415;
    /**
     * 416 Range Not Satisfiable.
     *
     * The range specified by the `Range` header field in the request can't be
     * fulfilled; it's possible that the range is outside the size of the target
     * URI's data.
     *
     * @see RequestHeader::RANGE
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/416
     */
    public const int RANGE_NOT_SATISFIABLE = 416;
    /**
     * 417 Expectation Failed.
     *
     * This response code means the expectation indicated by the `Expect`
     * request header field can't be met by the server.
     *
     * @see RequestHeader::EXPECT
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/417
     */
    public const int EXPECTATION_FAILED = 417;
    /**
     * 418 I'm a teapot.
     *
     * The server refuses the attempt to brew coffee with a teapot.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/418
     */
    public const int IM_A_TEAPOT = 418;
    /**
     * 421 Misdirected Request.
     *
     * The request was directed at a server that is not able to produce a
     * response. This can be sent by a server that is not configured to produce
     * responses for the combination of scheme and authority that are included
     * in the request URI.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/421
     */
    public const int MISDIRECTED_REQUEST = 421;
    /**
     * 422 Unprocessable Entity (WebDAV).
     *
     * The request was well-formed but was unable to be followed due to semantic
     * errors.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/422
     */
    public const int UNPROCESSABLE_ENTITY = 422;
    /**
     * 423 Locked (WebDAV).
     *
     * The resource that is being accessed is locked.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/423
     */
    public const int LOCKED = 423;
    /**
     * 424 Failed Dependency (WebDAV).
     *
     * The request failed due to failure of a previous request.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/424
     */
    public const int FAILED_DEPENDENCY = 424;
    /**
     * 425 Too Early.
     *
     * Indicates that the server is unwilling to risk processing a request that
     * might be replayed.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/425
     */
    public const int TOO_EARLY = 425;
    /**
     * 426 Upgrade Required.
     *
     * The server refuses to perform the request using the current protocol but
     * might be willing to do so after the client upgrades to a different
     * protocol. The server sends an `Upgrade` header in a 426 response to
     * indicate the required protocol(s).
     *
     * @see Header::UPGRADE
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/426
     */
    public const int UPGRADE_REQUIRED = 426;
    /**
     * 428 Precondition Required.
     *
     * The origin server requires the request to be conditional. This response
     * is intended to prevent the 'lost update' problem, where a client GETs a
     * resource's state, modifies it, and PUTs it back to the server, when
     * meanwhile a third party has modified the state on the server, leading to
     * a conflict.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/428
     */
    public const int PRECONDITION_REQUIRED = 428;
    /**
     * 429 Too Many Requests.
     *
     * The user has sent too many requests in a given amount of time ("rate
     * limiting").
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/429
     */
    public const int TOO_MANY_REQUESTS = 429;
    /**
     * 431 Request Header Fields Too Large.
     *
     * The server is unwilling to process the request because its header fields
     * are too large. The request may be resubmitted after reducing the size of
     * the request header fields.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/431
     */
    public const int REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    /**
     * 451 Unavailable For Legal Reasons.
     *
     * The user-agent requested a resource that cannot legally be provided, such
     * as a web page censored by a government.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/451
     */
    public const int UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    /**
     * 499 Client Closed Request (nginx).
     *
     * A non-standard status code introduced by nginx for the case when a client
     * closes the connection while nginx is processing the request.
     *
     * @see https://httpstatuses.com/499
     */
    public const int CLIENT_CLOSED_REQUEST = 499;
    // -------------------------------------------------------------------------
    // Server error responses
    // -------------------------------------------------------------------------
    /**
     * 500 Internal Server Error.
     *
     * The server has encountered a situation it doesn't know how to handle.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/500
     */
    public const int INTERNAL_SERVER_ERROR = 500;
    /**
     * 501 Not Implemented.
     *
     * The request method is not supported by the server and cannot be handled.
     * The only methods that servers are required to support (and therefore that
     * must not return this code) are `GET` and `HEAD`.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/501
     */
    public const int NOT_IMPLEMENTED = 501;
    /**
     * 502 Bad Gateway.
     *
     * This error response means that the server, while working as a gateway to
     * get a response needed to handle the request, got an invalid response.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/502
     */
    public const int BAD_GATEWAY = 502;
    /**
     * 503 Service Unavailable.
     *
     * The server is not ready to handle the request. Common causes are a server
     * that is down for maintenance or that is overloaded. Note that together
     * with this response, a user-friendly page explaining the problem should be
     * sent. This response should be used for temporary conditions and the
     * `Retry-After:` HTTP header should, if possible, contain the estimated
     * time before the recovery of the service. The webmaster must also take
     * care about the caching-related headers that are sent along with this
     * response, as these temporary condition responses should usually not be
     * cached.
     *
     * @see ResponseHeader::RETRY_AFTER
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/503
     */
    public const int SERVICE_UNAVAILABLE = 503;
    /**
     * 504 Gateway Timeout.
     *
     * This error response is given when the server is acting as a gateway and
     * cannot get a response in time.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/504
     */
    public const int GATEWAY_TIMEOUT = 504;
    /**
     * 505 HTTP Version Not Supported.
     *
     * The HTTP version used in the request is not supported by the server.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/505
     */
    public const int HTTP_VERSION_NOT_SUPPORTED = 505;
    /**
     * 506 Variant Also Negotiates.
     *
     * The server has an internal configuration error: the chosen variant
     * resource is configured to engage in transparent content negotiation
     * itself, and is therefore not a proper end point in the negotiation
     * process.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/506
     */
    public const int VARIANT_ALSO_NEGOTIATES = 506;
    /**
     * 507 Insufficient Storage (WebDAV).
     *
     * The method could not be performed on the resource because the server is
     * unable to store the representation needed to successfully complete the
     * request.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/507
     */
    public const int INSUFFICIENT_STORAGE = 507;
    /**
     * 508 Loop Detected (WebDAV).
     *
     * The server detected an infinite loop while processing the request.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/508
     */
    public const int LOOP_DETECTED = 508;
    /**
     * 510 Not Extended.
     *
     * Further extensions to the request are required for the server to fulfill
     * it.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/510
     */
    public const int NOT_EXTENDED = 510;
    /**
     * 511 Network Authentication Required.
     *
     * The 511 status code indicates that the client needs to authenticate to
     * gain network access.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/511
     */
    public const int NETWORK_AUTHENTICATION_REQUIRED = 511;
    /**
     * 599 Network Connect Timeout Error.
     *
     * This status code is not specified in any RFCs, but is used by some HTTP
     * proxies to signal a network connect timeout behind the proxy to a client
     * in front of the proxy.
     *
     * @see https://httpstatuses.com/599
     */
    public const int NETWORK_CONNECT_TIMEOUT_ERROR = 599;
    /**
     * Response status codes and reasons.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
     *
     * @var array<int,string>
     */
    protected static array $status = [
        // ---------------------------------------------------------------------
        // Informational responses
        // ---------------------------------------------------------------------
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // ---------------------------------------------------------------------
        // Successful responses
        // ---------------------------------------------------------------------
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // ---------------------------------------------------------------------
        // Redirection messages
        // ---------------------------------------------------------------------
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // ---------------------------------------------------------------------
        // Client error responses
        // ---------------------------------------------------------------------
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot",
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        // ---------------------------------------------------------------------
        // Server error responses
        // ---------------------------------------------------------------------
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    /**
     * @param int $code
     * @param string|null $default
     *
     * @throws InvalidArgumentException for invalid code
     * @throws LogicException for unknown code without a default reason
     *
     * @return string
     */
    public static function getReason(int $code, ?string $default = null) : string
    {
        $code = static::validate($code);
        if (isset(static::$status[$code])) {
            return static::$status[$code];
        }
        if ($default !== null) {
            return $default;
        }
        throw new LogicException('Unknown status code must have a default reason: ' . $code);
    }

    public static function setStatus(int $code, string $reason) : void
    {
        static::$status[static::validate($code)] = $reason;
    }

    public static function validate(int $code) : int
    {
        if ($code < 100 || $code > 599) {
            throw new InvalidArgumentException('Invalid response status code: ' . $code);
        }
        return $code;
    }
}
