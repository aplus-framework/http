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
    \Framework\HTTP\Protocol::HTTP_1_0,
    \Framework\HTTP\Protocol::HTTP_1_1,
    \Framework\HTTP\Protocol::HTTP_2_0,
    \Framework\HTTP\Protocol::HTTP_2,
    \Framework\HTTP\Protocol::HTTP_3,
    'HTTP/1.0',
    'HTTP/1.1',
    'HTTP/2.0',
    'HTTP/2',
    'HTTP/3',
);
registerArgumentsSet(
    'methods',
    \Framework\HTTP\Method::CONNECT,
    \Framework\HTTP\Method::DELETE,
    \Framework\HTTP\Method::GET,
    \Framework\HTTP\Method::HEAD,
    \Framework\HTTP\Method::OPTIONS,
    \Framework\HTTP\Method::PATCH,
    \Framework\HTTP\Method::POST,
    \Framework\HTTP\Method::PUT,
    \Framework\HTTP\Method::TRACE,
    'CONNECT',
    'DELETE',
    'GET',
    'HEAD',
    'OPTIONS',
    'PATCH',
    'POST',
    'PUT',
    'TRACE',
);
registerArgumentsSet(
    'response_status_codes',
    \Framework\HTTP\Status::CONTINUE,
    \Framework\HTTP\Status::SWITCHING_PROTOCOLS,
    \Framework\HTTP\Status::PROCESSING,
    \Framework\HTTP\Status::EARLY_HINTS,
    \Framework\HTTP\Status::OK,
    \Framework\HTTP\Status::CREATED,
    \Framework\HTTP\Status::ACCEPTED,
    \Framework\HTTP\Status::NON_AUTHORITATIVE_INFORMATION,
    \Framework\HTTP\Status::NO_CONTENT,
    \Framework\HTTP\Status::RESET_CONTENT,
    \Framework\HTTP\Status::PARTIAL_CONTENT,
    \Framework\HTTP\Status::MULTI_STATUS,
    \Framework\HTTP\Status::ALREADY_REPORTED,
    \Framework\HTTP\Status::IM_USED,
    \Framework\HTTP\Status::MULTIPLE_CHOICES,
    \Framework\HTTP\Status::MOVED_PERMANENTLY,
    \Framework\HTTP\Status::FOUND,
    \Framework\HTTP\Status::SEE_OTHER,
    \Framework\HTTP\Status::NOT_MODIFIED,
    \Framework\HTTP\Status::USE_PROXY,
    \Framework\HTTP\Status::SWITCH_PROXY,
    \Framework\HTTP\Status::TEMPORARY_REDIRECT,
    \Framework\HTTP\Status::PERMANENT_REDIRECT,
    \Framework\HTTP\Status::BAD_REQUEST,
    \Framework\HTTP\Status::UNAUTHORIZED,
    \Framework\HTTP\Status::PAYMENT_REQUIRED,
    \Framework\HTTP\Status::FORBIDDEN,
    \Framework\HTTP\Status::NOT_FOUND,
    \Framework\HTTP\Status::METHOD_NOT_ALLOWED,
    \Framework\HTTP\Status::NOT_ACCEPTABLE,
    \Framework\HTTP\Status::PROXY_AUTHENTICATION_REQUIRED,
    \Framework\HTTP\Status::REQUEST_TIMEOUT,
    \Framework\HTTP\Status::CONFLICT,
    \Framework\HTTP\Status::GONE,
    \Framework\HTTP\Status::LENGTH_REQUIRED,
    \Framework\HTTP\Status::PRECONDITION_FAILED,
    \Framework\HTTP\Status::PAYLOAD_TOO_LARGE,
    \Framework\HTTP\Status::URI_TOO_LARGE,
    \Framework\HTTP\Status::UNSUPPORTED_MEDIA_TYPE,
    \Framework\HTTP\Status::RANGE_NOT_SATISFIABLE,
    \Framework\HTTP\Status::EXPECTATION_FAILED,
    \Framework\HTTP\Status::IM_A_TEAPOT,
    \Framework\HTTP\Status::MISDIRECTED_REQUEST,
    \Framework\HTTP\Status::UNPROCESSABLE_ENTITY,
    \Framework\HTTP\Status::LOCKED,
    \Framework\HTTP\Status::FAILED_DEPENDENCY,
    \Framework\HTTP\Status::TOO_EARLY,
    \Framework\HTTP\Status::UPGRADE_REQUIRED,
    \Framework\HTTP\Status::PRECONDITION_REQUIRED,
    \Framework\HTTP\Status::TOO_MANY_REQUESTS,
    \Framework\HTTP\Status::REQUEST_HEADER_FIELDS_TOO_LARGE,
    \Framework\HTTP\Status::UNAVAILABLE_FOR_LEGAL_REASONS,
    \Framework\HTTP\Status::CLIENT_CLOSED_REQUEST,
    \Framework\HTTP\Status::INTERNAL_SERVER_ERROR,
    \Framework\HTTP\Status::NOT_IMPLEMENTED,
    \Framework\HTTP\Status::BAD_GATEWAY,
    \Framework\HTTP\Status::SERVICE_UNAVAILABLE,
    \Framework\HTTP\Status::GATEWAY_TIMEOUT,
    \Framework\HTTP\Status::CODE_VERSION_NOT_SUPPORTED,
    \Framework\HTTP\Status::VARIANT_ALSO_NEGOTIATES,
    \Framework\HTTP\Status::INSUFFICIENT_STORAGE,
    \Framework\HTTP\Status::LOOP_DETECTED,
    \Framework\HTTP\Status::NOT_EXTENDED,
    \Framework\HTTP\Status::NETWORK_AUTHENTICATION_REQUIRED,
    \Framework\HTTP\Status::NETWORK_CONNECT_TIMEOUT_ERROR,
);
registerArgumentsSet(
    'response_redirect_codes',
    \Framework\HTTP\Status::MULTIPLE_CHOICES,
    \Framework\HTTP\Status::MOVED_PERMANENTLY,
    \Framework\HTTP\Status::FOUND,
    \Framework\HTTP\Status::SEE_OTHER,
    \Framework\HTTP\Status::NOT_MODIFIED,
    \Framework\HTTP\Status::USE_PROXY,
    \Framework\HTTP\Status::SWITCH_PROXY,
    \Framework\HTTP\Status::TEMPORARY_REDIRECT,
    \Framework\HTTP\Status::PERMANENT_REDIRECT,
);
registerArgumentsSet(
    'request_headers',
    \Framework\HTTP\RequestHeader::ACCEPT,
    \Framework\HTTP\RequestHeader::ACCEPT_CHARSET,
    \Framework\HTTP\RequestHeader::ACCEPT_ENCODING,
    \Framework\HTTP\RequestHeader::ACCEPT_LANGUAGE,
    \Framework\HTTP\RequestHeader::ACCESS_CONTROL_REQUEST_HEADERS,
    \Framework\HTTP\RequestHeader::ACCESS_CONTROL_REQUEST_METHOD,
    \Framework\HTTP\RequestHeader::AUTHORIZATION,
    \Framework\HTTP\RequestHeader::CACHE_CONTROL,
    \Framework\HTTP\RequestHeader::CONNECTION,
    \Framework\HTTP\RequestHeader::CONTENT_DISPOSITION,
    \Framework\HTTP\RequestHeader::CONTENT_ENCODING,
    \Framework\HTTP\RequestHeader::CONTENT_LANGUAGE,
    \Framework\HTTP\RequestHeader::CONTENT_LENGTH,
    \Framework\HTTP\RequestHeader::CONTENT_LOCATION,
    \Framework\HTTP\RequestHeader::CONTENT_RANGE,
    \Framework\HTTP\RequestHeader::CONTENT_TYPE,
    \Framework\HTTP\RequestHeader::COOKIE,
    \Framework\HTTP\RequestHeader::DATE,
    \Framework\HTTP\RequestHeader::DNT,
    \Framework\HTTP\RequestHeader::EXPECT,
    \Framework\HTTP\RequestHeader::FORWARDED,
    \Framework\HTTP\RequestHeader::FROM,
    \Framework\HTTP\RequestHeader::HOST,
    \Framework\HTTP\RequestHeader::IF_MATCH,
    \Framework\HTTP\RequestHeader::IF_MODIFIED_SINCE,
    \Framework\HTTP\RequestHeader::IF_NONE_MATCH,
    \Framework\HTTP\RequestHeader::IF_RANGE,
    \Framework\HTTP\RequestHeader::IF_UNMODIFIED_SINCE,
    \Framework\HTTP\RequestHeader::KEEP_ALIVE,
    \Framework\HTTP\RequestHeader::LINK,
    \Framework\HTTP\RequestHeader::ORIGIN,
    \Framework\HTTP\RequestHeader::PRAGMA,
    \Framework\HTTP\RequestHeader::PROXY_AUTHORIZATION,
    \Framework\HTTP\RequestHeader::RANGE,
    \Framework\HTTP\RequestHeader::REFERER,
    \Framework\HTTP\RequestHeader::SEC_FETCH_DEST,
    \Framework\HTTP\RequestHeader::SEC_FETCH_MODE,
    \Framework\HTTP\RequestHeader::SEC_FETCH_SITE,
    \Framework\HTTP\RequestHeader::SEC_FETCH_USER,
    \Framework\HTTP\RequestHeader::TE,
    \Framework\HTTP\RequestHeader::TRAILER,
    \Framework\HTTP\RequestHeader::TRANSFER_ENCODING,
    \Framework\HTTP\RequestHeader::UPGRADE,
    \Framework\HTTP\RequestHeader::UPGRADE_INSECURE_REQUESTS,
    \Framework\HTTP\RequestHeader::USER_AGENT,
    \Framework\HTTP\RequestHeader::VIA,
    \Framework\HTTP\RequestHeader::WARNING,
    \Framework\HTTP\RequestHeader::X_FORWARDED_FOR,
    \Framework\HTTP\RequestHeader::X_FORWARDED_HOST,
    \Framework\HTTP\RequestHeader::X_FORWARDED_PROTO,
    \Framework\HTTP\RequestHeader::X_REAL_IP,
    \Framework\HTTP\RequestHeader::X_REQUESTED_WITH,
    \Framework\HTTP\RequestHeader::X_REQUEST_ID,
    'Accept',
    'Accept-Charset',
    'Accept-Encoding',
    'Accept-Language',
    'Access-Control-Request-Headers',
    'Access-Control-Request-Method',
    'Authorization',
    'Cache-Control',
    'Connection',
    'Content-Disposition',
    'Content-Encoding',
    'Content-Language',
    'Content-Length',
    'Content-Location',
    'Content-Range',
    'Content-Type',
    'Cookie',
    'DNT',
    'Date',
    'Expect',
    'Forwarded',
    'From',
    'Host',
    'If-Match',
    'If-Modified-Since',
    'If-None-Match',
    'If-Range',
    'If-Unmodified-Since',
    'Keep-Alive',
    'Link',
    'Origin',
    'Pragma',
    'Proxy-Authorization',
    'Range',
    'Referer',
    'Sec-Fetch-Dest',
    'Sec-Fetch-Mode',
    'Sec-Fetch-Site',
    'Sec-Fetch-User',
    'TE',
    'Trailer',
    'Transfer-Encoding',
    'Upgrade',
    'Upgrade-Insecure-Requests',
    'User-Agent',
    'Via',
    'Warning',
    'X-Forwarded-For',
    'X-Forwarded-Host',
    'X-Forwarded-Proto',
    'X-Request-ID',
    'X-Real-IP',
    'X-Requested-With',
);
registerArgumentsSet(
    'response_headers',
    \Framework\HTTP\ResponseHeader::ACCEPT_RANGES,
    \Framework\HTTP\ResponseHeader::ACCESS_CONTROL_ALLOW_CREDENTIALS,
    \Framework\HTTP\ResponseHeader::ACCESS_CONTROL_ALLOW_HEADERS,
    \Framework\HTTP\ResponseHeader::ACCESS_CONTROL_ALLOW_METHODS,
    \Framework\HTTP\ResponseHeader::ACCESS_CONTROL_ALLOW_ORIGIN,
    \Framework\HTTP\ResponseHeader::ACCESS_CONTROL_EXPOSE_HEADERS,
    \Framework\HTTP\ResponseHeader::ACCESS_CONTROL_MAX_AGE,
    \Framework\HTTP\ResponseHeader::AGE,
    \Framework\HTTP\ResponseHeader::ALLOW,
    \Framework\HTTP\ResponseHeader::CACHE_CONTROL,
    \Framework\HTTP\ResponseHeader::CLEAR_SITE_DATA,
    \Framework\HTTP\ResponseHeader::CONNECTION,
    \Framework\HTTP\ResponseHeader::CONTENT_DISPOSITION,
    \Framework\HTTP\ResponseHeader::CONTENT_ENCODING,
    \Framework\HTTP\ResponseHeader::CONTENT_LANGUAGE,
    \Framework\HTTP\ResponseHeader::CONTENT_LENGTH,
    \Framework\HTTP\ResponseHeader::CONTENT_LOCATION,
    \Framework\HTTP\ResponseHeader::CONTENT_RANGE,
    \Framework\HTTP\ResponseHeader::CONTENT_SECURITY_POLICY,
    \Framework\HTTP\ResponseHeader::CONTENT_SECURITY_POLICY_REPORT_ONLY,
    \Framework\HTTP\ResponseHeader::CONTENT_TYPE,
    \Framework\HTTP\ResponseHeader::DATE,
    \Framework\HTTP\ResponseHeader::ETAG,
    \Framework\HTTP\ResponseHeader::EXPECT_CT,
    \Framework\HTTP\ResponseHeader::EXPIRES,
    \Framework\HTTP\ResponseHeader::FEATURE_POLICY,
    \Framework\HTTP\ResponseHeader::KEEP_ALIVE,
    \Framework\HTTP\ResponseHeader::LAST_MODIFIED,
    \Framework\HTTP\ResponseHeader::LINK,
    \Framework\HTTP\ResponseHeader::LOCATION,
    \Framework\HTTP\ResponseHeader::PRAGMA,
    \Framework\HTTP\ResponseHeader::PROXY_AUTHENTICATE,
    \Framework\HTTP\ResponseHeader::PUBLIC_KEY_PINS,
    \Framework\HTTP\ResponseHeader::PUBLIC_KEY_PINS_REPORT_ONLY,
    \Framework\HTTP\ResponseHeader::REFERRER_POLICY,
    \Framework\HTTP\ResponseHeader::RETRY_AFTER,
    \Framework\HTTP\ResponseHeader::SERVER,
    \Framework\HTTP\ResponseHeader::SET_COOKIE,
    \Framework\HTTP\ResponseHeader::SOURCEMAP,
    \Framework\HTTP\ResponseHeader::STRICT_TRANSPORT_SECURITY,
    \Framework\HTTP\ResponseHeader::TIMING_ALLOW_ORIGIN,
    \Framework\HTTP\ResponseHeader::TK,
    \Framework\HTTP\ResponseHeader::TRAILER,
    \Framework\HTTP\ResponseHeader::TRANSFER_ENCODING,
    \Framework\HTTP\ResponseHeader::UPGRADE,
    \Framework\HTTP\ResponseHeader::VARY,
    \Framework\HTTP\ResponseHeader::VIA,
    \Framework\HTTP\ResponseHeader::WARNING,
    \Framework\HTTP\ResponseHeader::WWW_AUTHENTICATE,
    \Framework\HTTP\ResponseHeader::X_CONTENT_TYPE_OPTIONS,
    \Framework\HTTP\ResponseHeader::X_DNS_PREFETCH_CONTROL,
    \Framework\HTTP\ResponseHeader::X_FRAME_OPTIONS,
    \Framework\HTTP\ResponseHeader::X_POWERED_BY,
    \Framework\HTTP\ResponseHeader::X_REQUEST_ID,
    \Framework\HTTP\ResponseHeader::X_XSS_PROTECTION,
    'Accept-Ranges',
    'Access-Control-Allow-Credentials',
    'Access-Control-Allow-Headers',
    'Access-Control-Allow-Methods',
    'Access-Control-Allow-Origin',
    'Access-Control-Expose-Headers',
    'Access-Control-Max-Age',
    'Age',
    'Allow',
    'Cache-Control',
    'Clear-Site-Data',
    'Connection',
    'Content-Disposition',
    'Content-Encoding',
    'Content-Language',
    'Content-Length',
    'Content-Location',
    'Content-Range',
    'Content-Security-Policy',
    'Content-Security-Policy-Report-Only',
    'Content-Type',
    'Date',
    'ETag',
    'Expect-CT',
    'Expires',
    'Feature-Policy',
    'Keep-Alive',
    'Last-Modified',
    'Link',
    'Location',
    'Pragma',
    'Proxy-Authenticate',
    'Public-Key-Pins',
    'Public-Key-Pins-Report-Only',
    'Referrer-Policy',
    'Retry-After',
    'Server',
    'Set-Cookie',
    'SourceMap',
    'Strict-Transport-Security',
    'Timing-Allow-Origin',
    'Tk',
    'Trailer',
    'Transfer-Encoding',
    'Upgrade',
    'Vary',
    'Via',
    'WWW-Authenticate',
    'Warning',
    'X-Content-Type-Options',
    'X-DNS-Prefetch-Control',
    'X-Frame-Options',
    'X-Powered-By',
    'X-Request-ID',
    'X-XSS-Protection',
);
registerArgumentsSet(
    'response_headers_multiline',
    \Framework\HTTP\ResponseHeader::DATE,
    \Framework\HTTP\ResponseHeader::EXPIRES,
    \Framework\HTTP\ResponseHeader::LAST_MODIFIED,
    \Framework\HTTP\ResponseHeader::PROXY_AUTHENTICATE,
    \Framework\HTTP\ResponseHeader::RETRY_AFTER,
    \Framework\HTTP\ResponseHeader::SET_COOKIE,
    \Framework\HTTP\ResponseHeader::WWW_AUTHENTICATE,
    'Date',
    'Expires',
    'Last-Modified',
    'Proxy-Authenticate',
    'Retry-After',
    'Set-Cookie',
    'WWW-Authenticate',
);
registerArgumentsSet(
    'cookie_samesite',
    'Lax',
    'None',
    'Strict',
    'Unset',
);
registerArgumentsSet(
    'url_schemes',
    'http',
    'https',
);
registerArgumentsSet(
    'fastcgi_params',
    'CONTENT_LENGTH',
    'CONTENT_TYPE',
    'DOCUMENT_ROOT',
    'DOCUMENT_URI',
    'GATEWAY_INTERFACE',
    'HTTPS',
    'QUERY_STRING',
    'REMOTE_ADDR',
    'REMOTE_PORT',
    'REMOTE_USER',
    'REQUEST_METHOD',
    'REQUEST_SCHEME',
    'REQUEST_URI',
    'SCRIPT_FILENAME',
    'SCRIPT_NAME',
    'SERVER_ADDR',
    'SERVER_NAME',
    'SERVER_PORT',
    'SERVER_PROTOCOL',
    'SERVER_SOFTWARE',
);
registerArgumentsSet(
    'hash_algos',
    'adler32',
    'crc32',
    'crc32b',
    'crc32c',
    'fnv132',
    'fnv164',
    'fnv1a32',
    'fnv1a64',
    'gost',
    'gost-crypto',
    'haval128,3',
    'haval128,4',
    'haval128,5',
    'haval160,3',
    'haval160,4',
    'haval160,5',
    'haval192,3',
    'haval192,4',
    'haval192,5',
    'haval224,3',
    'haval224,4',
    'haval224,5',
    'haval256,3',
    'haval256,4',
    'haval256,5',
    'joaat',
    'md2',
    'md4',
    'md5',
    'murmur3a',
    'murmur3c',
    'murmur3f',
    'ripemd128',
    'ripemd160',
    'ripemd256',
    'ripemd320',
    'sha1',
    'sha224',
    'sha256',
    'sha3-224',
    'sha3-256',
    'sha3-384',
    'sha3-512',
    'sha384',
    'sha512',
    'sha512/224',
    'sha512/256',
    'snefru',
    'snefru256',
    'tiger128,3',
    'tiger128,4',
    'tiger160,3',
    'tiger160,4',
    'tiger192,3',
    'tiger192,4',
    'whirlpool',
    'xxh128',
    'xxh3',
    'xxh32',
    'xxh64',
);
registerArgumentsSet(
    'csp_directives',
    \Framework\HTTP\CSP::baseUri,
    \Framework\HTTP\CSP::childSrc,
    \Framework\HTTP\CSP::connectSrc,
    \Framework\HTTP\CSP::defaultSrc,
    \Framework\HTTP\CSP::fontSrc,
    \Framework\HTTP\CSP::formAction,
    \Framework\HTTP\CSP::frameAncestors,
    \Framework\HTTP\CSP::frameSrc,
    \Framework\HTTP\CSP::imgSrc,
    \Framework\HTTP\CSP::manifestSrc,
    \Framework\HTTP\CSP::mediaSrc,
    \Framework\HTTP\CSP::navigateTo,
    \Framework\HTTP\CSP::objectSrc,
    \Framework\HTTP\CSP::pluginTypes,
    \Framework\HTTP\CSP::prefetchSrc,
    \Framework\HTTP\CSP::reportTo,
    \Framework\HTTP\CSP::reportUri,
    \Framework\HTTP\CSP::sandbox,
    \Framework\HTTP\CSP::scriptSrc,
    \Framework\HTTP\CSP::scriptSrcAttr,
    \Framework\HTTP\CSP::scriptSrcElem,
    \Framework\HTTP\CSP::styleSrc,
    \Framework\HTTP\CSP::styleSrcAttr,
    \Framework\HTTP\CSP::styleSrcElem,
    \Framework\HTTP\CSP::upgradeInsecureRequests,
    \Framework\HTTP\CSP::workerSrc,
    'base-uri',
    'child-src',
    'connect-src',
    'default-src',
    'font-src',
    'form-action',
    'frame-ancestors',
    'frame-src',
    'img-src',
    'manifest-src',
    'media-src',
    'navigate-to',
    'object-src',
    'plugin-types',
    'prefetch-src',
    'report-to',
    'report-uri',
    'sandbox',
    'script-src',
    'script-src-attr',
    'script-src-elem',
    'style-src',
    'style-src-attr',
    'style-src-elem',
    'upgrade-insecure-requests',
    'worker-src',
);
registerArgumentsSet(
    'csp_algos',
    'sha256',
    'sha384',
    'sha512',
);
expectedReturnValues(
    \Framework\HTTP\MessageInterface::getProtocol(),
    argumentsSet('protocols')
);
expectedReturnValues(
    \Framework\HTTP\RequestInterface::getMethod(),
    argumentsSet('methods')
);
expectedReturnValues(
    \Framework\HTTP\ResponseInterface::getStatusCode(),
    argumentsSet('response_status_codes')
);
expectedArguments(
    \Framework\HTTP\Message::setProtocol(),
    0,
    argumentsSet('protocols')
);
expectedArguments(
    \Framework\HTTP\Message::setMethod(),
    0,
    argumentsSet('methods')
);
expectedArguments(
    \Framework\HTTP\Message::isMethod(),
    0,
    argumentsSet('methods')
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
    0,
    argumentsSet('fastcgi_params')
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
    \Framework\HTTP\Request::setJsonFlags(),
    0,
    argumentsSet('json_decode_flags')
);
expectedArguments(
    \Framework\HTTP\Response::setJson(),
    1,
    argumentsSet('json_encode_flags')
);
expectedArguments(
    \Framework\HTTP\Response::setJsonFlags(),
    0,
    argumentsSet('json_encode_flags')
);
expectedArguments(
    \Framework\HTTP\Message::setStatusCode(),
    0,
    argumentsSet('response_status_codes')
);
expectedArguments(
    \Framework\HTTP\Message::isStatusCode(),
    0,
    argumentsSet('response_status_codes')
);
expectedArguments(
    \Framework\HTTP\Response::setStatus(),
    0,
    argumentsSet('response_status_codes')
);
expectedArguments(
    \Framework\HTTP\RequestInterface::getHeader(),
    0,
    argumentsSet('request_headers')
);
expectedArguments(
    \Framework\HTTP\Request::getHeader(),
    0,
    argumentsSet('request_headers')
);
expectedArguments(
    \Framework\HTTP\RequestInterface::hasHeader(),
    0,
    argumentsSet('request_headers')
);
expectedArguments(
    \Framework\HTTP\Request::hasHeader(),
    0,
    argumentsSet('request_headers')
);
expectedArguments(
    \Framework\HTTP\ResponseInterface::getHeader(),
    0,
    argumentsSet('response_headers')
);
expectedArguments(
    \Framework\HTTP\Response::getHeader(),
    0,
    argumentsSet('response_headers')
);
expectedArguments(
    \Framework\HTTP\ResponseInterface::hasHeader(),
    0,
    argumentsSet('response_headers')
);
expectedArguments(
    \Framework\HTTP\Response::hasHeader(),
    0,
    argumentsSet('response_headers')
);
expectedArguments(
    \Framework\HTTP\ResponseInterface::setHeader(),
    0,
    argumentsSet('response_headers')
);
expectedArguments(
    \Framework\HTTP\Response::setHeader(),
    0,
    argumentsSet('response_headers')
);
expectedArguments(
    \Framework\HTTP\Response::appendHeader(),
    0,
    argumentsSet('response_headers_multiline')
);
expectedArguments(
    \Framework\HTTP\Response::redirect(),
    2,
    argumentsSet('response_redirect_codes')
);
expectedArguments(
    \Framework\HTTP\Response::setAutoEtag(),
    1,
    argumentsSet('hash_algos')
);
expectedArguments(
    \Framework\HTTP\Cookie::setSameSite(),
    0,
    argumentsSet('cookie_samesite')
);
expectedArguments(
    \Framework\HTTP\URL::setScheme(),
    0,
    argumentsSet('url_schemes')
);
expectedArguments(
    \Framework\HTTP\CSP::addOptions(),
    0,
    argumentsSet('csp_directives')
);
expectedArguments(
    \Framework\HTTP\CSP::setDirective(),
    0,
    argumentsSet('csp_directives')
);
expectedArguments(
    \Framework\HTTP\CSP::getDirective(),
    0,
    argumentsSet('csp_directives')
);
expectedArguments(
    \Framework\HTTP\CSP::removeDirective(),
    0,
    argumentsSet('csp_directives')
);
expectedArguments(
    \Framework\HTTP\CSP::makeHash(),
    0,
    argumentsSet('csp_algos')
);
expectedArguments(
    \Framework\HTTP\CSP::makeHashes(),
    1,
    argumentsSet('csp_algos')
);
expectedReturnValues(
    \Framework\HTTP\Request::getAuthType(),
    'Basic',
    'Bearer',
    'Digest',
    null,
);
expectedReturnValues(
    \Framework\HTTP\UploadedFile::getError(),
    \UPLOAD_ERR_OK,
    \UPLOAD_ERR_INI_SIZE,
    \UPLOAD_ERR_FORM_SIZE,
    \UPLOAD_ERR_PARTIAL,
    \UPLOAD_ERR_NO_FILE,
    \UPLOAD_ERR_NO_TMP_DIR,
    \UPLOAD_ERR_CANT_WRITE,
    \UPLOAD_ERR_EXTENSION,
);
expectedReturnValues(
    \Framework\HTTP\UserAgent::getType(),
    'Browser',
    'Robot',
    'Unknown',
);
