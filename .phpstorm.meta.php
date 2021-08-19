<?php
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPSTORM_META;

registerArgumentsSet(
    'filters',
    \FILTER_CALLBACK,
    \FILTER_SANITIZE_ADD_SLASHES,
    \FILTER_SANITIZE_EMAIL,
    \FILTER_SANITIZE_ENCODED,
    \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    \FILTER_SANITIZE_NUMBER_FLOAT,
    \FILTER_SANITIZE_NUMBER_INT,
    \FILTER_SANITIZE_SPECIAL_CHARS,
    \FILTER_SANITIZE_STRING,
    \FILTER_SANITIZE_STRIPPED,
    \FILTER_SANITIZE_URL,
    \FILTER_UNSAFE_RAW,
    \FILTER_VALIDATE_BOOL,
    \FILTER_VALIDATE_BOOLEAN,
    \FILTER_VALIDATE_DOMAIN,
    \FILTER_VALIDATE_EMAIL,
    \FILTER_VALIDATE_FLOAT,
    \FILTER_VALIDATE_INT,
    \FILTER_VALIDATE_IP,
    \FILTER_VALIDATE_MAC,
    \FILTER_VALIDATE_REGEXP,
    \FILTER_VALIDATE_URL,
);
registerArgumentsSet(
    'json_decode_flags',
    \JSON_BIGINT_AS_STRING,
    \JSON_INVALID_UTF8_IGNORE,
    \JSON_INVALID_UTF8_SUBSTITUTE,
    \JSON_OBJECT_AS_ARRAY,
    \JSON_THROW_ON_ERROR,
);
registerArgumentsSet(
    'json_encode_flags',
    \JSON_FORCE_OBJECT,
    \JSON_HEX_AMP,
    \JSON_HEX_APOS,
    \JSON_HEX_QUOT,
    \JSON_HEX_TAG,
    \JSON_INVALID_UTF8_IGNORE,
    \JSON_INVALID_UTF8_SUBSTITUTE,
    \JSON_NUMERIC_CHECK,
    \JSON_PARTIAL_OUTPUT_ON_ERROR,
    \JSON_PRESERVE_ZERO_FRACTION,
    \JSON_PRETTY_PRINT,
    \JSON_THROW_ON_ERROR,
    \JSON_UNESCAPED_LINE_TERMINATORS,
    \JSON_UNESCAPED_SLASHES,
    \JSON_UNESCAPED_UNICODE,
);
registerArgumentsSet(
    'protocols',
    \Framework\HTTP\MessageInterface::PROTOCOL_HTTP_1_0,
    \Framework\HTTP\MessageInterface::PROTOCOL_HTTP_1_1,
    \Framework\HTTP\MessageInterface::PROTOCOL_HTTP_2_0,
    \Framework\HTTP\MessageInterface::PROTOCOL_HTTP_2,
    \Framework\HTTP\MessageInterface::PROTOCOL_HTTP_3,
);
registerArgumentsSet(
    'response_status_codes',
    \Framework\HTTP\ResponseInterface::CODE_CONTINUE,
    \Framework\HTTP\ResponseInterface::CODE_SWITCHING_PROTOCOLS,
    \Framework\HTTP\ResponseInterface::CODE_PROCESSING,
    \Framework\HTTP\ResponseInterface::CODE_EARLY_HINTS,
    \Framework\HTTP\ResponseInterface::CODE_OK,
    \Framework\HTTP\ResponseInterface::CODE_CREATED,
    \Framework\HTTP\ResponseInterface::CODE_ACCEPTED,
    \Framework\HTTP\ResponseInterface::CODE_NON_AUTHORITATIVE_INFORMATION,
    \Framework\HTTP\ResponseInterface::CODE_NO_CONTENT,
    \Framework\HTTP\ResponseInterface::CODE_RESET_CONTENT,
    \Framework\HTTP\ResponseInterface::CODE_PARTIAL_CONTENT,
    \Framework\HTTP\ResponseInterface::CODE_MULTI_STATUS,
    \Framework\HTTP\ResponseInterface::CODE_ALREADY_REPORTED,
    \Framework\HTTP\ResponseInterface::CODE_IM_USED,
    \Framework\HTTP\ResponseInterface::CODE_MULTIPLE_CHOICES,
    \Framework\HTTP\ResponseInterface::CODE_MOVED_PERMANENTLY,
    \Framework\HTTP\ResponseInterface::CODE_FOUND,
    \Framework\HTTP\ResponseInterface::CODE_SEE_OTHER,
    \Framework\HTTP\ResponseInterface::CODE_NOT_MODIFIED,
    \Framework\HTTP\ResponseInterface::CODE_USE_PROXY,
    \Framework\HTTP\ResponseInterface::CODE_SWITCH_PROXY,
    \Framework\HTTP\ResponseInterface::CODE_TEMPORARY_REDIRECT,
    \Framework\HTTP\ResponseInterface::CODE_PERMANENT_REDIRECT,
    \Framework\HTTP\ResponseInterface::CODE_BAD_REQUEST,
    \Framework\HTTP\ResponseInterface::CODE_UNAUTHORIZED,
    \Framework\HTTP\ResponseInterface::CODE_PAYMENT_REQUIRED,
    \Framework\HTTP\ResponseInterface::CODE_FORBIDDEN,
    \Framework\HTTP\ResponseInterface::CODE_NOT_FOUND,
    \Framework\HTTP\ResponseInterface::CODE_METHOD_NOT_ALLOWED,
    \Framework\HTTP\ResponseInterface::CODE_NOT_ACCEPTABLE,
    \Framework\HTTP\ResponseInterface::CODE_PROXY_AUTHENTICATION_REQUIRED,
    \Framework\HTTP\ResponseInterface::CODE_REQUEST_TIMEOUT,
    \Framework\HTTP\ResponseInterface::CODE_CONFLICT,
    \Framework\HTTP\ResponseInterface::CODE_GONE,
    \Framework\HTTP\ResponseInterface::CODE_LENGTH_REQUIRED,
    \Framework\HTTP\ResponseInterface::CODE_PRECONDITION_FAILED,
    \Framework\HTTP\ResponseInterface::CODE_PAYLOAD_TOO_LARGE,
    \Framework\HTTP\ResponseInterface::CODE_URI_TOO_LARGE,
    \Framework\HTTP\ResponseInterface::CODE_UNSUPPORTED_MEDIA_TYPE,
    \Framework\HTTP\ResponseInterface::CODE_RANGE_NOT_SATISFIABLE,
    \Framework\HTTP\ResponseInterface::CODE_EXPECTATION_FAILED,
    \Framework\HTTP\ResponseInterface::CODE_IM_A_TEAPOT,
    \Framework\HTTP\ResponseInterface::CODE_MISDIRECTED_REQUEST,
    \Framework\HTTP\ResponseInterface::CODE_UNPROCESSABLE_ENTITY,
    \Framework\HTTP\ResponseInterface::CODE_LOCKED,
    \Framework\HTTP\ResponseInterface::CODE_FAILED_DEPENDENCY,
    \Framework\HTTP\ResponseInterface::CODE_TOO_EARLY,
    \Framework\HTTP\ResponseInterface::CODE_UPGRADE_REQUIRED,
    \Framework\HTTP\ResponseInterface::CODE_PRECONDITION_REQUIRED,
    \Framework\HTTP\ResponseInterface::CODE_TOO_MANY_REQUESTS,
    \Framework\HTTP\ResponseInterface::CODE_REQUEST_HEADER_FIELDS_TOO_LARGE,
    \Framework\HTTP\ResponseInterface::CODE_UNAVAILABLE_FOR_LEGAL_REASONS,
    \Framework\HTTP\ResponseInterface::CODE_CLIENT_CLOSED_REQUEST,
    \Framework\HTTP\ResponseInterface::CODE_INTERNAL_SERVER_ERROR,
    \Framework\HTTP\ResponseInterface::CODE_NOT_IMPLEMENTED,
    \Framework\HTTP\ResponseInterface::CODE_BAD_GATEWAY,
    \Framework\HTTP\ResponseInterface::CODE_SERVICE_UNAVAILABLE,
    \Framework\HTTP\ResponseInterface::CODE_GATEWAY_TIMEOUT,
    \Framework\HTTP\ResponseInterface::CODE_CODE_VERSION_NOT_SUPPORTED,
    \Framework\HTTP\ResponseInterface::CODE_VARIANT_ALSO_NEGOTIATES,
    \Framework\HTTP\ResponseInterface::CODE_INSUFFICIENT_STORAGE,
    \Framework\HTTP\ResponseInterface::CODE_LOOP_DETECTED,
    \Framework\HTTP\ResponseInterface::CODE_NOT_EXTENDED,
    \Framework\HTTP\ResponseInterface::CODE_NETWORK_AUTHENTICATION_REQUIRED,
    \Framework\HTTP\ResponseInterface::CODE_NETWORK_CONNECT_TIMEOUT_ERROR,
);
registerArgumentsSet(
    'request_headers',
    \Framework\HTTP\MessageInterface::HEADER_CACHE_CONTROL,
    \Framework\HTTP\MessageInterface::HEADER_CONNECTION,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_DISPOSITION,
    \Framework\HTTP\MessageInterface::HEADER_DATE,
    \Framework\HTTP\MessageInterface::HEADER_KEEP_ALIVE,
    \Framework\HTTP\MessageInterface::HEADER_VIA,
    \Framework\HTTP\MessageInterface::HEADER_WARNING,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_ENCODING,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_LANGUAGE,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_LOCATION,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_TYPE,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_LENGTH,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_RANGE,
    \Framework\HTTP\MessageInterface::HEADER_TRAILER,
    \Framework\HTTP\MessageInterface::HEADER_TRANSFER_ENCODING,
    \Framework\HTTP\RequestInterface::HEADER_ACCEPT,
    \Framework\HTTP\RequestInterface::HEADER_ACCEPT_CHARSET,
    \Framework\HTTP\RequestInterface::HEADER_ACCEPT_ENCODING,
    \Framework\HTTP\RequestInterface::HEADER_ACCEPT_LANGUAGE,
    \Framework\HTTP\RequestInterface::HEADER_ACCESS_CONTROL_REQUEST_HEADERS,
    \Framework\HTTP\RequestInterface::HEADER_ACCESS_CONTROL_REQUEST_METHOD,
    \Framework\HTTP\RequestInterface::HEADER_AUTHORIZATION,
    \Framework\HTTP\RequestInterface::HEADER_COOKIE,
    \Framework\HTTP\RequestInterface::HEADER_DNT,
    \Framework\HTTP\RequestInterface::HEADER_EXPECT,
    \Framework\HTTP\RequestInterface::HEADER_FORWARDED,
    \Framework\HTTP\RequestInterface::HEADER_FROM,
    \Framework\HTTP\RequestInterface::HEADER_HOST,
    \Framework\HTTP\RequestInterface::HEADER_IF_MATCH,
    \Framework\HTTP\RequestInterface::HEADER_IF_MODIFIED_SINCE,
    \Framework\HTTP\RequestInterface::HEADER_IF_NONE_MATCH,
    \Framework\HTTP\RequestInterface::HEADER_IF_RANGE,
    \Framework\HTTP\RequestInterface::HEADER_IF_UNMODIFIED_SINCE,
    \Framework\HTTP\RequestInterface::HEADER_ORIGIN,
    \Framework\HTTP\RequestInterface::HEADER_PROXY_AUTHORIZATION,
    \Framework\HTTP\RequestInterface::HEADER_RANGE,
    \Framework\HTTP\RequestInterface::HEADER_REFERER,
    \Framework\HTTP\RequestInterface::HEADER_TE,
    \Framework\HTTP\RequestInterface::HEADER_UPGRADE_INSECURE_REQUESTS,
    \Framework\HTTP\RequestInterface::HEADER_USER_AGENT,
    \Framework\HTTP\RequestInterface::HEADER_X_FORWARDED_FOR,
    \Framework\HTTP\RequestInterface::HEADER_X_FORWARDED_HOST,
    \Framework\HTTP\RequestInterface::HEADER_X_FORWARDED_PROTO,
    \Framework\HTTP\RequestInterface::HEADER_X_REQUESTED_WITH,
);
registerArgumentsSet(
    'response_headers',
    \Framework\HTTP\MessageInterface::HEADER_CACHE_CONTROL,
    \Framework\HTTP\MessageInterface::HEADER_CONNECTION,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_DISPOSITION,
    \Framework\HTTP\MessageInterface::HEADER_DATE,
    \Framework\HTTP\MessageInterface::HEADER_KEEP_ALIVE,
    \Framework\HTTP\MessageInterface::HEADER_VIA,
    \Framework\HTTP\MessageInterface::HEADER_WARNING,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_ENCODING,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_LANGUAGE,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_LOCATION,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_TYPE,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_LENGTH,
    \Framework\HTTP\MessageInterface::HEADER_CONTENT_RANGE,
    \Framework\HTTP\MessageInterface::HEADER_TRAILER,
    \Framework\HTTP\MessageInterface::HEADER_TRANSFER_ENCODING,
    \Framework\HTTP\ResponseInterface::HEADER_ACCEPT_RANGES,
    \Framework\HTTP\ResponseInterface::HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS,
    \Framework\HTTP\ResponseInterface::HEADER_ACCESS_CONTROL_ALLOW_HEADERS,
    \Framework\HTTP\ResponseInterface::HEADER_ACCESS_CONTROL_ALLOW_METHODS,
    \Framework\HTTP\ResponseInterface::HEADER_ACCESS_CONTROL_ALLOW_ORIGIN,
    \Framework\HTTP\ResponseInterface::HEADER_ACCESS_CONTROL_EXPOSE_HEADERS,
    \Framework\HTTP\ResponseInterface::HEADER_ACCESS_CONTROL_MAX_AGE,
    \Framework\HTTP\ResponseInterface::HEADER_AGE,
    \Framework\HTTP\ResponseInterface::HEADER_ALLOW,
    \Framework\HTTP\ResponseInterface::HEADER_CLEAR_SITE_DATA,
    \Framework\HTTP\ResponseInterface::HEADER_CONTENT_SECURITY_POLICY,
    \Framework\HTTP\ResponseInterface::HEADER_CONTENT_SECURITY_POLICY_REPORT_ONLY,
    \Framework\HTTP\ResponseInterface::HEADER_ETAG,
    \Framework\HTTP\ResponseInterface::HEADER_EXPECT_CT,
    \Framework\HTTP\ResponseInterface::HEADER_EXPIRES,
    \Framework\HTTP\ResponseInterface::HEADER_FEATURE_POLICY,
    \Framework\HTTP\ResponseInterface::HEADER_LAST_MODIFIED,
    \Framework\HTTP\ResponseInterface::HEADER_LOCATION,
    \Framework\HTTP\ResponseInterface::HEADER_PROXY_AUTHENTICATE,
    \Framework\HTTP\ResponseInterface::HEADER_PUBLIC_KEY_PINS,
    \Framework\HTTP\ResponseInterface::HEADER_PUBLIC_KEY_PINS_REPORT_ONLY,
    \Framework\HTTP\ResponseInterface::HEADER_REFERRER_POLICY,
    \Framework\HTTP\ResponseInterface::HEADER_RETRY_AFTER,
    \Framework\HTTP\ResponseInterface::HEADER_SERVER,
    \Framework\HTTP\ResponseInterface::HEADER_SET_COOKIE,
    \Framework\HTTP\ResponseInterface::HEADER_SOURCEMAP,
    \Framework\HTTP\ResponseInterface::HEADER_STRICT_TRANSPORT_SECURITY,
    \Framework\HTTP\ResponseInterface::HEADER_TIMING_ALLOW_ORIGIN,
    \Framework\HTTP\ResponseInterface::HEADER_TK,
    \Framework\HTTP\ResponseInterface::HEADER_VARY,
    \Framework\HTTP\ResponseInterface::HEADER_WWW_AUTHENTICATE,
    \Framework\HTTP\ResponseInterface::HEADER_X_CONTENT_TYPE_OPTIONS,
    \Framework\HTTP\ResponseInterface::HEADER_X_DNS_PREFETCH_CONTROL,
    \Framework\HTTP\ResponseInterface::HEADER_X_FRAME_OPTIONS,
    \Framework\HTTP\ResponseInterface::HEADER_X_XSS_PROTECTION,
    \Framework\HTTP\ResponseInterface::HEADER_X_REQUEST_ID,
    \Framework\HTTP\ResponseInterface::HEADER_X_POWERED_BY,
);
expectedArguments(
    \Framework\HTTP\Message::setProtocol(),
    0,
    argumentsSet('protocols')
);
expectedArguments(
    \Framework\HTTP\Request::filterInput(),
    2,
    argumentsSet('filters')
);
expectedArguments(
    \Framework\HTTP\Request::getEnv(),
    1,
    argumentsSet('filters')
);
expectedArguments(
    \Framework\HTTP\Request::getGet(),
    1,
    argumentsSet('filters')
);
expectedArguments(
    \Framework\HTTP\Request::getPost(),
    1,
    argumentsSet('filters')
);
expectedArguments(
    \Framework\HTTP\Request::getServer(),
    1,
    argumentsSet('filters')
);
expectedArguments(
    \Framework\HTTP\Request::getJson(),
    1,
    argumentsSet('json_decode_flags')
);
expectedArguments(
    \Framework\HTTP\Response::setJson(),
    1,
    argumentsSet('json_encode_flags')
);
expectedArguments(
    \Framework\HTTP\Response::setStatusCode(),
    0,
    argumentsSet('response_status_codes')
);
expectedArguments(
    \Framework\HTTP\Response::setStatusLine(),
    0,
    argumentsSet('response_status_codes')
);
expectedArguments(
    \Framework\HTTP\Request::getHeader(),
    0,
    argumentsSet('request_headers')
);
expectedArguments(
    \Framework\HTTP\Request::getHeaderName(),
    0,
    argumentsSet('request_headers')
);
expectedArguments(
    \Framework\HTTP\Response::getHeader(),
    0,
    argumentsSet('response_headers')
);
expectedArguments(
    \Framework\HTTP\Response::setHeader(),
    0,
    argumentsSet('response_headers')
);
expectedArguments(
    \Framework\HTTP\Response::getHeaderName(),
    0,
    argumentsSet('response_headers')
);
