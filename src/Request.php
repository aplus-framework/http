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

use BadMethodCallException;
use Closure;
use Framework\Helpers\ArraySimple;
use InvalidArgumentException;
use LogicException;
use Override;
use RequestParseBodyException;
use stdClass;
use UnexpectedValueException;

/**
 * Class Request.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Messages#http_requests
 *
 * @package http
 */
class Request extends Message implements RequestInterface
{
    use Traits\ContentNegotiationTrait;
    use Traits\RequestAuthTrait;
    use Traits\RequestIpTrait;
    /**
     * @var array<string,UploadedFile|array<mixed>>|null
     */
    protected ?array $files = null;
    /**
     * @var array<int,array<mixed>>|null
     */
    protected ?array $parsedBody = null;
    /**
     * @var array<string,mixed>|null
     */
    protected ?array $parseBodyOptions = null;
    protected int $port;
    protected URL | false | null $referrer = null;
    protected UserAgent | false | null $userAgent = null;
    protected bool $isAjax;
    /**
     * Tell if is an HTTPS connection.
     *
     * @var bool|null
     */
    protected ?bool $isSecure;
    /**
     * @var array<string>
     */
    protected array $allowedHosts = [];

    /**
     * Request constructor.
     *
     * @param array{
     *      allowed_hosts?: array<string>,
     *      ip_key?: Closure|string,
     * } $config
     *
     * @throws UnexpectedValueException if invalid Host
     * @throws InvalidArgumentException for IP key not set in the $_SERVER var
     */
    public function __construct(array $config = [])
    {
        if (isset($config['allowed_hosts'])) {
            $this->setAllowedHosts($config['allowed_hosts']);
        }
        if (isset($config['ip_key'])) {
            $this->setIpKey($config['ip_key']);
        }
        $this->setIp();
        $this->prepareStatusLine();
    }

    /**
     * @param string $method
     * @param array<int,mixed> $arguments
     *
     * @throws BadMethodCallException for method not allowed or method not found
     *
     * @return static
     */
    public function __call(string $method, array $arguments)
    {
        if ($method === 'setBody') {
            return $this->setBody(...$arguments);
        }
        if (\method_exists($this, $method)) {
            throw new BadMethodCallException("Method not allowed: {$method}");
        }
        throw new BadMethodCallException("Method not found: {$method}");
    }

    #[Override]
    public function toString() : string
    {
        if ($this->parseContentType() === 'multipart/form-data') {
            $this->setBody($this->getMultipartBody());
        }
        return parent::toString();
    }

    /**
     * NOTE: Created by comparing a request made with Firefox.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types#multipartform-data
     *
     * @return string
     */
    protected function getMultipartBody() : string
    {
        $bodyParts = [];
        /**
         * @var array<string,string> $parsedBody
         */
        $parsedBody = ArraySimple::convert($this->getParsedBody());
        foreach ($parsedBody as $field => $value) {
            $field = \htmlspecialchars($field, \ENT_QUOTES | \ENT_HTML5);
            $bodyParts[] = \implode("\r\n", [
                "Content-Disposition: form-data; name=\"{$field}\"",
                '',
                $value,
            ]);
        }
        /**
         * @var array<string,UploadedFile> $files
         */
        $files = ArraySimple::convert($this->getFiles());
        foreach ($files as $field => $file) {
            $field = \htmlspecialchars($field, \ENT_QUOTES | \ENT_HTML5);
            $field = \preg_replace('/\[\d+]/', '[]', $field);
            $filename = \htmlspecialchars($file->getFullPath(), \ENT_QUOTES | \ENT_HTML5);
            $contentType = $file->getClientType() ?: 'application/octet-stream';
            $getContentsOf = $file->isMoved() ? $file->getDestination() : $file->getTmpName();
            $data = '';
            if ($getContentsOf !== '') {
                $data = \file_get_contents($getContentsOf);
            }
            $bodyParts[] = \implode("\r\n", [
                "Content-Disposition: form-data; name=\"{$field}\"; filename=\"{$filename}\"",
                'Content-Type: ' . $contentType,
                '',
                $data,
            ]);
        }
        $boundary = '';
        $boundaryParts = \explode(';', $this->getContentType());
        foreach ($boundaryParts as $boundaryPart) {
            $boundaryPart = \trim($boundaryPart);
            if (\str_starts_with(\strtolower($boundaryPart), 'boundary=')) {
                $boundary = \substr($boundaryPart, \strlen('boundary='));
                break;
            }
        }
        foreach ($bodyParts as &$part) {
            $part = "--{$boundary}\r\n{$part}";
        }
        unset($part);
        $bodyParts[] = "--{$boundary}--";
        $bodyParts[] = '';
        $bodyParts = \implode("\r\n", $bodyParts);
        /**
         * Uncomment the code below to make a raw test.
         *
         * @see \Tests\HTTP\RequestTest::testToStringMultipart()
         */
        /*
        $serverLength = (string) $_SERVER['CONTENT_LENGTH'];
        $algoLength = (string) \strlen($bodyParts);
        \var_dump($serverLength, $algoLength);
        if ($serverLength !== $algoLength) {
            throw new \Exception(
                '$_SERVER CONTENT_LENGTH is ' . $serverLength
                . ', but the algorithm calculated ' . $algoLength
            );
        }
        */
        return $bodyParts;
    }

    /**
     * Check if Host header is allowed. And set allowed hosts.
     *
     * @see https://expressionengine.com/blog/http-host-and-server-name-security-issues
     * @see http://nginx.org/en/docs/http/request_processing.html
     *
     * @param array<string> $allowedHosts
     *
     * @return static
     */
    protected function setAllowedHosts(array $allowedHosts) : static
    {
        if (empty($allowedHosts)) {
            $this->allowedHosts = $allowedHosts;
            return $this;
        }
        $host = $_SERVER['HTTP_HOST'] ?? null;
        if (!\in_array($host, $allowedHosts, true)) {
            throw new UnexpectedValueException('Invalid Host: ' . $host);
        }
        $this->allowedHosts = $allowedHosts;
        return $this;
    }

    /**
     * Get the allowed hosts.
     *
     * @return array<string>
     */
    public function getAllowedHosts() : array
    {
        return $this->allowedHosts;
    }

    protected function prepareStatusLine() : void
    {
        $this->setProtocol($_SERVER['SERVER_PROTOCOL']);
        $this->setMethod($_SERVER['REQUEST_METHOD']);
        $url = $this->isSecure() ? 'https' : 'http';
        $url .= '://' . $_SERVER['HTTP_HOST'];
        $url .= $_SERVER['REQUEST_URI'];
        $this->setUrl($url);
    }

    public function getHeader(string $name) : ?string
    {
        $this->prepareHeaders();
        return $this->headers[\strtolower($name)] ?? null;
    }

    /**
     * @return array<string,string>
     */
    public function getHeaders() : array
    {
        $this->prepareHeaders();
        return $this->headers;
    }

    protected function prepareHeaders() : void
    {
        if (!empty($this->headers)) {
            return;
        }
        foreach ($_SERVER as $name => $value) {
            if (\str_starts_with($name, 'HTTP_')) {
                $name = \strtr(\substr($name, 5), ['_' => '-']);
                $this->setHeader($name, $value);
            }
        }
    }

    public function getCookie(string $name) : ?Cookie
    {
        $this->prepareCookies();
        return $this->cookies[$name] ?? null;
    }

    /**
     * Get all Cookies.
     *
     * @return array<string,Cookie>
     */
    public function getCookies() : array
    {
        $this->prepareCookies();
        return $this->cookies;
    }

    protected function prepareCookies() : void
    {
        if (!empty($this->cookies)) {
            return;
        }
        foreach ($_COOKIE as $name => $value) {
            $this->setCookie(new Cookie($name, $value));
        }
    }

    /**
     * @see https://www.php.net/manual/en/wrappers.php.php#wrappers.php.input
     *
     * @return string
     */
    public function getBody() : string
    {
        if (!isset($this->body)) {
            $this->body = (string) \file_get_contents('php://input');
        }
        return $this->body;
    }

    protected function prepareFiles() : void
    {
        if (isset($this->files)) {
            return;
        }
        $this->files = $this->getInputFiles();
    }

    /**
     * @param int $type
     * @param string|null $name
     * @param int|null $filter
     * @param array<int,int>|int $options
     *
     * @see https://www.php.net/manual/en/function.filter-var
     * @see https://www.php.net/manual/en/filter.filters.php
     *
     * @return mixed
     */
    protected function filterInput(
        int $type,
        ?string $name = null,
        ?int $filter = null,
        array | int $options = 0
    ) : mixed {
        $input = match ($type) {
            \INPUT_POST => $_POST,
            \INPUT_GET => $_GET,
            \INPUT_COOKIE => $_COOKIE,
            \INPUT_ENV => $_ENV,
            \INPUT_SERVER => $_SERVER,
            default => throw new InvalidArgumentException('Invalid input type: ' . $type)
        };
        if ($name !== null) {
            $input = \in_array($type, [\INPUT_POST, \INPUT_GET], true)
                ? ArraySimple::value($name, $input)
                : $input[$name] ?? null;
        }
        if ($filter !== null) {
            $input = \filter_var($input, $filter, $options);
        }
        return $input;
    }

    /**
     * Force an HTTPS connection on same URL.
     */
    public function forceHttps() : void
    {
        if (!$this->isSecure()) {
            \header(
                'Location: ' . $this->getUrl()->setScheme('https'),
                true,
                Status::MOVED_PERMANENTLY
            );
            if (!\defined('TESTING')) {
                // @codeCoverageIgnoreStart
                exit;
                // @codeCoverageIgnoreEnd
            }
        }
    }

    /**
     * Parses and filters the request body.
     *
     * Get data when the request the Content-Type is
     * `application/x-www-form-urlencoded` or `multipart/form-data`.
     *
     * Files can be obtained using the {@see Framework\HTTP\Request::getFiles()}
     * method.
     *
     * @param string|null $name The array key name as a string, or null to return all data
     * @param int|null $filter Optional filter applied to the value returned with `$name`
     * @param array<int,int>|int $filterOptions Filter options
     *
     * @throws RequestParseBodyException for errors when parsing the body
     *
     * @return mixed Returns the value according to the `$name` and the filtering
     */
    public function getParsedBody(
        ?string $name = null,
        ?int $filter = null,
        array | int $filterOptions = 0
    ) : mixed {
        if ($this->isMethod(Method::POST)) {
            return $this->getPost($name, $filter, $filterOptions);
        }
        return $this->getFilteredParsedBody($name, $filter, $filterOptions);
    }

    /**
     * Parses and filters the request body.
     *
     * Get data when the request the Content-Type is
     * `application/x-www-form-urlencoded` or `multipart/form-data`.
     *
     * Files can be obtained using the {@see Framework\HTTP\Request::getFiles()}
     * method.
     *
     * @param string|null $name The array key name as a string, or null to return all data
     * @param int|null $filter Optional filter applied to the value returned with `$name`
     * @param array<int,int>|int $filterOptions Filter options
     *
     * @throws RequestParseBodyException for errors when parsing the body
     *
     * @return mixed Returns the value according to the `$name` and the filtering
     */
    protected function getFilteredParsedBody(
        ?string $name = null,
        ?int $filter = null,
        array | int $filterOptions = 0
    ) : mixed {
        if (!$this->isParsedBody()) {
            $this->parseBody($this->getParseBodyOptions());
        }
        $variable = $name === null
            ? $this->parsedBody[0]
            : ArraySimple::value($name, $this->parsedBody[0]);
        return $filter === null
            ? $variable
            : \filter_var($variable, $filter, $filterOptions);
    }

    /**
     * Parses the request body.
     *
     * @param array{
     *      max_file_uploads?: int|string,
     *      max_input_vars?: int|string,
     *      max_multipart_body_parts?: int|string,
     *      post_max_size?: int|string,
     *      upload_max_filesize?: int|string,
     * }|null $options Options used in {@see request_parse_body()}
     *
     * @throws RequestParseBodyException for errors when parsing the body
     * @throws LogicException when the body has already been parsed
     *
     * @return static
     */
    public function parseBody(?array $options = null) : static
    {
        if ($this->isParsedBody()) {
            throw new LogicException('Parse error: the request body has already been parsed');
        }
        $options ??= $this->getParseBodyOptions();
        $this->parsedBody = $this->requestParseBody($options);
        return $this;
    }

    /**
     * Call the request_parse_body() function.
     *
     * @param array{
     *      max_file_uploads?: int|string,
     *      max_input_vars?: int|string,
     *      max_multipart_body_parts?: int|string,
     *      post_max_size?: int|string,
     *      upload_max_filesize?: int|string,
     * }|null $options
     *
     * @throws RequestParseBodyException for errors when parsing the body
     *
     * @return array<int,array<mixed>>
     */
    protected function requestParseBody(?array $options = null) : array
    {
        return \request_parse_body($options);
    }

    public function isParsedBody() : bool
    {
        return isset($this->parsedBody);
    }

    /**
     * @return array{
     *      max_file_uploads?: int|string,
     *      max_input_vars?: int|string,
     *      max_multipart_body_parts?: int|string,
     *      post_max_size?: int|string,
     *      upload_max_filesize?: int|string,
     * }|null
     */
    public function getParseBodyOptions() : ?array
    {
        return $this->parseBodyOptions;
    }

    /**
     * @param array{
     *      max_file_uploads?: int|string,
     *      max_input_vars?: int|string,
     *      max_multipart_body_parts?: int|string,
     *      post_max_size?: int|string,
     *      upload_max_filesize?: int|string,
     * }|null $options
     *
     * @see https://www.php.net/manual/en/function.request-parse-body.php#refsect1-function.request-parse-body-parameters
     */
    public function setParseBodyOptions(?array $options) : static
    {
        $this->parseBodyOptions = $options;
        return $this;
    }

    /**
     * Get the request body as JSON.
     *
     * @param bool|null $associative When true, JSON objects will be returned as
     * associative arrays; when false, JSON objects will be returned as objects.
     * When null, JSON objects will be returned as associative arrays or objects
     * depending on whether JSON_OBJECT_AS_ARRAY is set in the flags.
     * @param int|null $flags [optional] <p>
     *  Bitmask consisting of <b>JSON_BIGINT_AS_STRING</b>,
     *  <b>JSON_INVALID_UTF8_IGNORE</b>,
     *  <b>JSON_INVALID_UTF8_SUBSTITUTE</b>,
     *  <b>JSON_OBJECT_AS_ARRAY</b>,
     *  <b>JSON_THROW_ON_ERROR</b>.
     *  </p>
     *  <p>Default is none when null.</p>
     * @param int<1,max> $depth user specified recursion depth
     *
     * @see https://www.php.net/manual/en/function.json-decode.php
     * @see https://www.php.net/manual/en/json.constants.php
     * @see Framework\HTTP\Message::getJsonFlags()
     * @see Framework\HTTP\Message::setJsonFlags()
     *
     * @return array<mixed>|false|stdClass If option JSON_THROW_ON_ERROR
     * is not set, return false if json_decode fail. Otherwise, return a
     * stdClass instance, or an array if the $associative argument is passed as
     * true.
     */
    public function getJson(
        ?bool $associative = null,
        ?int $flags = null,
        int $depth = 512
    ) : array | false | stdClass {
        if ($flags === null) {
            $flags = $this->getJsonFlags();
        }
        $body = \json_decode($this->getBody(), $associative, $depth, $flags);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            return false;
        }
        return $body;
    }

    /**
     * Get the Content-Type header value.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
     *
     * @return string|null
     */
    public function getContentType() : ?string
    {
        return $_SERVER['HTTP_CONTENT_TYPE'] ?? null;
    }

    /**
     * @param string|null $name
     * @param int|null $filter
     * @param array<int,int>|int $filterOptions
     *
     * @return mixed
     */
    public function getEnv(
        ?string $name = null,
        ?int $filter = null,
        array | int $filterOptions = 0
    ) : mixed {
        return $this->filterInput(\INPUT_ENV, $name, $filter, $filterOptions);
    }

    /**
     * @return array<string,UploadedFile|array<mixed>>
     */
    public function getFiles() : array
    {
        $this->prepareFiles();
        return $this->files;
    }

    public function hasFiles() : bool
    {
        $this->prepareFiles();
        return !empty($this->files);
    }

    public function getFile(string $name) : ?UploadedFile
    {
        $this->prepareFiles();
        $file = ArraySimple::value($name, $this->files);
        return \is_array($file) ? null : $file;
    }

    /**
     * Get the URL GET queries.
     *
     * @param string|null $name
     * @param int|null $filter
     * @param array<int,int>|int $filterOptions
     *
     * @see Framework\HTTP\Method::GET
     *
     * @return mixed
     */
    public function getGet(
        ?string $name = null,
        ?int $filter = null,
        array | int $filterOptions = 0
    ) : mixed {
        return $this->filterInput(\INPUT_GET, $name, $filter, $filterOptions);
    }

    /**
     * Get the X-Request-ID header.
     *
     * @return string|null
     */
    public function getId() : ?string
    {
        return $_SERVER['HTTP_X_REQUEST_ID'] ?? null;
    }

    #[Override]
    public function getMethod() : string
    {
        return parent::getMethod();
    }

    /**
     * @param string $method
     *
     * @throws InvalidArgumentException for invalid method
     *
     * @return bool
     */
    #[Override]
    public function isMethod(string $method) : bool
    {
        return parent::isMethod($method);
    }

    /**
     * Gets data from the last request, if it was redirected.
     *
     * @param string|null $key a key name or null to get all data
     *
     * @see Response::redirect()
     *
     * @throws LogicException if PHP Session is not active to get redirect data
     *
     * @return mixed an array containing all data, the key value or null
     * if the key was not found
     */
    public function getRedirectData(?string $key = null) : mixed
    {
        static $data;
        if ($data === null && \session_status() !== \PHP_SESSION_ACTIVE) {
            throw new LogicException('Session must be active to get redirect data');
        }
        if ($data === null) {
            $data = $_SESSION['$']['redirect_data'] ?? false;
            unset($_SESSION['$']['redirect_data']);
        }
        if ($key !== null && $data) {
            return ArraySimple::value($key, $data);
        }
        return $data === false ? null : $data;
    }

    /**
     * Get the URL port.
     *
     * @return int
     */
    public function getPort() : int
    {
        return $this->port ?? $_SERVER['SERVER_PORT'];
    }

    /**
     * Make an empty value according to the name type.
     *
     * @param string|null $name
     *
     * @return array<mixed>|null
     */
    protected function makeEmptyValue(?string $name) : ?array
    {
        return $name === null ? [] : null;
    }

    /**
     * Get POST data.
     *
     * Get data when the request method is POST and the Content-Type is
     * `application/x-www-form-urlencoded` or `multipart/form-data`.
     *
     * Files can be obtained using the {@see Framework\HTTP\Request::getFiles()}
     * method.
     *
     * @param string|null $name The array key name as a string, or null to return all data
     * @param int|null $filter Optional filter applied to the value returned with `$name`
     * @param array<int,int>|int $filterOptions Filter options
     *
     * @see Framework\HTTP\Method::POST
     *
     * @throws RequestParseBodyException for errors when parsing the body
     *
     * @return mixed Returns the value according to the `$name` and the filtering
     */
    public function getPost(
        ?string $name = null,
        ?int $filter = null,
        array | int $filterOptions = 0
    ) : mixed {
        if ($this->isEnabledPostDataReading()) {
            return $this->filterInput(\INPUT_POST, $name, $filter, $filterOptions);
        }
        if ($this->isMethod(Method::POST)) {
            return $this->getFilteredParsedBody($name, $filter, $filterOptions);
        }
        return $this->makeEmptyValue($name);
    }

    /**
     * Get PATCH data.
     *
     * Get data when the request method is PATCH and the Content-Type is
     * `application/x-www-form-urlencoded` or `multipart/form-data`.
     *
     * Files can be obtained using the {@see Framework\HTTP\Request::getFiles()}
     * method.
     *
     * @param string|null $name The array key name as a string, or null to return all data
     * @param int|null $filter Optional filter applied to the value returned with `$name`
     * @param array<int,int>|int $filterOptions Filter options
     *
     * @see Framework\HTTP\Method::PATCH
     *
     * @throws RequestParseBodyException for errors when parsing the body
     *
     * @return mixed Returns the value according to the `$name` and the filtering
     */
    public function getPatch(
        ?string $name = null,
        ?int $filter = null,
        array | int $filterOptions = 0
    ) : mixed {
        if ($this->isMethod(Method::PATCH)) {
            return $this->getFilteredParsedBody($name, $filter, $filterOptions);
        }
        return $this->makeEmptyValue($name);
    }

    /**
     * Get PUT data.
     *
     * Get data when the request method is PUT and the Content-Type is
     * `application/x-www-form-urlencoded` or `multipart/form-data`.
     *
     * Files can be obtained using the {@see Framework\HTTP\Request::getFiles()}
     * method.
     *
     * @param string|null $name The array key name as a string, or null to return all data
     * @param int|null $filter Optional filter applied to the value returned with `$name`
     * @param array<int,int>|int $filterOptions Filter options
     *
     * @see Framework\HTTP\Method::PUT
     *
     * @throws RequestParseBodyException for errors when parsing the body
     *
     * @return mixed Returns the value according to the `$name` and the filtering
     */
    public function getPut(
        ?string $name = null,
        ?int $filter = null,
        array | int $filterOptions = 0
    ) : mixed {
        if ($this->isMethod(Method::PUT)) {
            return $this->getFilteredParsedBody($name, $filter, $filterOptions);
        }
        return $this->makeEmptyValue($name);
    }

    /**
     * Get QUERY data.
     *
     * Get the response body when the request method is QUERY.
     *
     * @see Framework\HTTP\Method::QUERY
     *
     * @return string|null the request body or null if it is not a request with
     * the QUERY method
     */
    public function getQuery() : ?string
    {
        if ($this->isMethod(Method::QUERY)) {
            return $this->getBody();
        }
        return null;
    }

    /**
     * Get the Referer header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referer
     *
     * @return URL|null
     */
    public function getReferer() : ?URL
    {
        if ($this->referrer instanceof URL) {
            return $this->referrer;
        }
        if ($this->referrer === false) {
            return null;
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        if ($referer === null) {
            $this->referrer = false;
            return null;
        }
        try {
            $this->referrer = new URL($referer);
        } catch (InvalidArgumentException) {
            $this->referrer = false;
        }
        return $this->getReferer();
    }

    /**
     * Get $_SERVER variables.
     *
     * @param string|null $name
     * @param int|null $filter
     * @param array<int,int>|int $filterOptions
     *
     * @return mixed
     */
    public function getServer(
        ?string $name = null,
        ?int $filter = null,
        array | int $filterOptions = 0
    ) : mixed {
        return $this->filterInput(\INPUT_SERVER, $name, $filter, $filterOptions);
    }

    /**
     * Gets the requested URL.
     *
     * @return URL
     */
    #[Override]
    public function getUrl() : URL
    {
        return parent::getUrl();
    }

    /**
     * Gets the User Agent client.
     *
     * @return UserAgent|null the UserAgent object or null if no user-agent
     * header was received
     */
    public function getUserAgent() : ?UserAgent
    {
        if ($this->userAgent instanceof UserAgent) {
            return $this->userAgent;
        }
        if ($this->userAgent === false) {
            return null;
        }
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        isset($userAgent)
            ? $this->setUserAgent($userAgent)
            : $this->userAgent = false;
        return $this->getUserAgent();
    }

    /**
     * @param UserAgent|string $userAgent
     *
     * @return static
     */
    protected function setUserAgent(UserAgent | string $userAgent) : static
    {
        if (!$userAgent instanceof UserAgent) {
            $userAgent = new UserAgent($userAgent);
        }
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * Check if is an AJAX Request based in the X-Requested-With Header.
     *
     * The X-Requested-With Header containing the "XMLHttpRequest" value is
     * used by various JavaScript libraries.
     *
     * @return bool
     */
    public function isAjax() : bool
    {
        if (isset($this->isAjax)) {
            return $this->isAjax;
        }
        $received = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? null;
        return $this->isAjax = ($received
            && \strtolower($received) === 'xmlhttprequest');
    }

    /**
     * Say if a connection has HTTPS.
     *
     * @return bool
     */
    public function isSecure() : bool
    {
        if (isset($this->isSecure)) {
            return $this->isSecure;
        }
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? '';
        $https = $_SERVER['HTTPS'] ?? '';
        return $this->isSecure = (
            \strtolower($scheme) === 'https' || \strtolower($https) === 'on'
        );
    }

    /**
     * Tells if the Content-Type header is application/x-www-form-urlencoded.
     *
     * @return bool
     */
    public function isFormUrlEncoded() : bool
    {
        return $this->parseContentType() === 'application/x-www-form-urlencoded';
    }

    /**
     * Tells if the Content-Type header is multipart/form-data.
     *
     * @return bool
     */
    public function isFormData() : bool
    {
        return $this->parseContentType() === 'multipart/form-data';
    }

    /**
     * Tells if the Content-Type header is application/x-www-form-urlencoded or
     * multipart/form-data.
     *
     * @return bool
     */
    public function isForm() : bool
    {
        return $this->isFormUrlEncoded() || $this->isFormData();
    }

    /**
     * Say if the request is a JSON call.
     *
     * @return bool
     */
    public function isJson() : bool
    {
        return $this->parseContentType() === 'application/json';
    }

    /**
     * Say if the request method is POST.
     *
     * @return bool
     */
    public function isPost() : bool
    {
        return $this->isMethod(Method::POST);
    }

    /**
     * @see https://php.watch/codex/enable_post_data_reading
     *
     * @return bool
     */
    protected function isEnabledPostDataReading() : bool
    {
        return \ini_get('enable_post_data_reading') === '1';
    }

    /**
     * @see https://www.sitepoint.com/community/t/-files-array-structure/2728/5
     *
     * @return array<string,UploadedFile|array<mixed>>
     */
    protected function getInputFiles() : array
    {
        if (!$this->isParsedBody() && !$this->isEnabledPostDataReading()) {
            $this->parseBody($this->getParseBodyOptions());
        }
        $files = $this->parsedBody[1] ?? $_FILES;
        if (empty($files)) {
            return [];
        }
        $makeObjects = static function (
            array $array,
            callable $makeObjects
        ) : UploadedFile | array {
            $return = [];
            foreach ($array as $k => $v) {
                if (\is_array($v)) {
                    $return[$k] = $makeObjects($v, $makeObjects);
                    continue;
                }
                /**
                 * @var array{
                 *      name: string,
                 *      type: string,
                 *      tmp_name: string,
                 *      error: int,
                 *      size: int,
                 *      full_path: string,
                 * } $array
                 */
                return new UploadedFile($array);
            }
            return $return;
        };
        return $makeObjects(ArraySimple::files($files), $makeObjects); // @phpstan-ignore-line
    }

    /**
     * Make a Response with the current Request.
     *
     * @return Response
     */
    public function makeResponse() : Response
    {
        return new Response($this);
    }
}
