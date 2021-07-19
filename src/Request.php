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
use Framework\Helpers\ArraySimple;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use LogicException;
use UnexpectedValueException;

/**
 * Class Request.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#HTTP_Requests
 */
class Request extends Message implements RequestInterface
{
    /**
     * @var array<string,array|UploadedFile>
     */
    protected array $files = [];
    /**
     * @var array<string,mixed>|null
     */
    protected ?array $parsedBody = null;
    /**
     * HTTP Authorization Header parsed.
     *
     * @var array<string,string|null>|null
     */
    protected ?array $auth = null;
    /**
     * @var string|null Basic or Digest
     */
    protected ?string $authType = null;
    protected string $host;
    protected int $port;
    /**
     * Request X-Request-ID header.
     */
    protected string | false $id;
    /**
     * @var array<string,array|null>
     */
    protected array $negotiation = [
        'ACCEPT' => null,
        'CHARSET' => null,
        'ENCODING' => null,
        'LANGUAGE' => null,
    ];
    protected false | URL $referrer;
    protected false | UserAgent $userAgent;
    protected bool $isAjax;
    /**
     * Tell if is a HTTPS connection.
     *
     * @var bool
     */
    protected bool $isSecure;

    /**
     * Request constructor.
     *
     * @param array<int,string>|null $allowedHosts set allowed hosts if your
     * server dont serve by Host header, as Nginx do
     *
     * @throws UnexpectedValueException if invalid Host
     */
    public function __construct(array $allowedHosts = null)
    {
        if ($allowedHosts !== null) {
            $this->validateHost($allowedHosts);
        }
        $this->prepareStatusLine();
        $this->prepareHeaders();
        $this->prepareCookies();
        $this->prepareUserAgent();
        $this->prepareFiles();
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

    public function __toString() : string
    {
        if ($this->parseContentType() === 'multipart/form-data') {
            $this->setBody($this->getMultipartBody());
        }
        return parent::__toString();
    }

    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types#multipartform-data
     *
     * @return string
     */
    protected function getMultipartBody() : string
    {
        $bodyParts = [];
        /**
         * @var array<string,string> $post
         */
        $post = ArraySimple::convert($this->getPOST());
        foreach ($post as $field => $value) {
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
            $filename = \htmlspecialchars($file->getName(), \ENT_QUOTES | \ENT_HTML5);
            $getContentsOf = $file->isMoved() ? $file->getDestination() : $file->getTmpName();
            $data = '';
            if ($getContentsOf !== '') {
                $data = \file_get_contents($getContentsOf);
            }
            $bodyParts[] = \implode("\r\n", [
                "Content-Disposition: form-data; name=\"{$field}\"; filename=\"{$filename}\"",
                'Content-Type: ' . $file->getClientType(),
                '',
                $data,
            ]);
        }
        $boundary = \explode(';', $this->getContentType(), 2);
        $boundary = \trim($boundary[1]);
        $boundary = \substr($boundary, \strlen('boundary='));
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
     * Check if Host header is allowed.
     *
     * @see https://expressionengine.com/blog/http-host-and-server-name-security-issues
     * @see http://nginx.org/en/docs/http/request_processing.html
     *
     * @param array<int,string> $allowedHosts
     */
    protected function validateHost(array $allowedHosts) : void
    {
        $host = $this->getServer('HTTP_HOST');
        if ( ! \in_array($host, $allowedHosts, true)) {
            throw new UnexpectedValueException('Invalid Host: ' . $host);
        }
    }

    protected function prepareStatusLine() : void
    {
        $this->setProtocol($this->getServer('SERVER_PROTOCOL'));
        $this->setMethod($this->getServer('REQUEST_METHOD'));
        $url = $this->isSecure() ? 'https' : 'http';
        $url .= '://' . $this->getServer('HTTP_HOST');
        //$url .= ':' . $this->getPort();
        $url .= $this->getServer('REQUEST_URI');
        $this->setUrl($url);
        $this->setHost($this->getUrl()->getHost());
    }

    protected function prepareHeaders() : void
    {
        foreach ($this->getServer() as $name => $value) {
            if ( ! \str_starts_with($name, 'HTTP_')) {
                continue;
            }
            $name = \strtr(\substr($name, 5), ['_' => '-']);
            $this->setHeader($name, $value);
        }
    }

    protected function prepareCookies() : void
    {
        foreach ($this->filterInput(\INPUT_COOKIE) as $name => $value) {
            $this->setCookie(new Cookie($name, $value));
        }
    }

    protected function prepareUserAgent() : void
    {
        $userAgent = $this->getServer('HTTP_USER_AGENT');
        if ($userAgent) {
            $this->setUserAgent($userAgent);
        }
    }

    /**
     * @see https://php.net/manual/en/wrappers.php.php#wrappers.php.input
     *
     * @return string
     */
    public function getBody() : string
    {
        if ($this->body) {
            return $this->body;
        }
        return (string) \file_get_contents('php://input');
    }

    protected function prepareFiles() : void
    {
        $this->files = $this->getInputFiles();
    }

    /**
     * @param int $type
     * @param string|null $variable
     * @param int|null $filter
     * @param array<int,int>|int $options
     *
     * @return mixed
     */
    protected function filterInput(
        int $type,
        string $variable = null,
        int $filter = null,
        array | int $options = []
    ) : mixed {
        $input = match ($type) {
            \INPUT_POST => $_POST,
            \INPUT_GET => $_GET,
            \INPUT_COOKIE => $_COOKIE,
            \INPUT_ENV => $_ENV,
            \INPUT_SERVER => $_SERVER,
            default => throw new \InvalidArgumentException('Invalid input type: ' . $type)
        };
        $variable = $variable === null
            ? $input
            : ArraySimple::value($variable, $input);
        return $filter
            ? \filter_var($variable, $filter, $options)
            : $variable;
    }

    /**
     * Force an HTTPS connection on same URL.
     */
    public function forceHttps() : void
    {
        if ( ! $this->isSecure()) {
            \header('Location: ' . $this->getURL()->setScheme('https')->getAsString(), true, 301);
            exit;
        }
    }

    /**
     * Get the Authorization type.
     *
     * @return string|null Basic, Digest or null for none
     */
    public function getAuthType() : ?string
    {
        if ($this->authType === null) {
            $auth = $this->getHeader(static::HEADER_AUTHORIZATION);
            if ($auth) {
                $this->parseAuth($auth);
            }
        }
        return $this->authType;
    }

    /**
     * Get Basic authorization.
     *
     * @return array<string>|null Two keys: username and password
     */
    #[ArrayShape(['username' => 'string|null', 'password' => 'string|null'])]
    public function getBasicAuth() : ?array
    {
        return $this->getAuthType() === 'Basic'
            ? $this->auth
            : null;
    }

    /**
     * Get Digest authorization.
     *
     * @return array<string>|null Nine keys: username, realm, nonce, uri,
     * response, opaque, qop, nc, cnonce
     */
    #[ArrayShape([
        'username' => 'string|null',
        'realm' => 'string|null',
        'nonce' => 'string|null',
        'uri' => 'string|null',
        'response' => 'string|null',
        'opaque' => 'string|null',
        'qop' => 'string|null',
        'nc' => 'string|null',
        'cnonce' => 'string|null',
    ])]
    public function getDigestAuth() : ?array
    {
        return $this->getAuthType() === 'Digest'
            ? $this->auth
            : null;
    }

    /**
     * @param string $authorization
     *
     * @return array<string,string|null>
     */
    protected function parseAuth(string $authorization) : array
    {
        $this->auth = [];
        [$type, $attributes] = \array_pad(\explode(' ', $authorization, 2), 2, null);
        if ($type === 'Basic') {
            $this->authType = $type;
            $this->auth = $this->parseBasicAuth($attributes);
        } elseif ($type === 'Digest') {
            $this->authType = $type;
            $this->auth = $this->parseDigestAuth($attributes);
        }
        return $this->auth;
    }

    /**
     * @param string $attributes
     *
     * @return array<string,string|null>
     */
    #[ArrayShape(['username' => 'string|null', 'password' => 'string|null'])]
    #[Pure]
    protected function parseBasicAuth(string $attributes) : array
    {
        $data = [
            'username' => null,
            'password' => null,
        ];
        $attributes = \base64_decode($attributes);
        if ($attributes) {
            [
                $data['username'],
                $data['password'],
            ] = \array_pad(\explode(':', $attributes, 2), 2, null);
        }
        return $data;
    }

    /**
     * @param string $attributes
     *
     * @return array<string,string|null>
     */
    #[ArrayShape([
        'username' => 'string|null',
        'realm' => 'string|null',
        'nonce' => 'string|null',
        'uri' => 'string|null',
        'response' => 'string|null',
        'opaque' => 'string|null',
        'qop' => 'string|null',
        'nc' => 'string|null',
        'cnonce' => 'string|null',
    ])]
    protected function parseDigestAuth(string $attributes) : array
    {
        $data = [
            'username' => null,
            'realm' => null,
            'nonce' => null,
            'uri' => null,
            'response' => null,
            'opaque' => null,
            'qop' => null,
            'nc' => null,
            'cnonce' => null,
        ];
        \preg_match_all(
            '#(username|realm|nonce|uri|response|opaque|qop|nc|cnonce)=(?:([\'"])([^\2]+?)\2|([^\s,]+))#',
            $attributes,
            $matches,
            \PREG_SET_ORDER
        );
        foreach ($matches as $match) {
            if (isset($match[1], $match[3])) {
                $data[$match[1]] = $match[3] ?: $match[4] ?? '';
            }
        }
        return $data; // @phpstan-ignore-line
    }

    /**
     * Get the Parsed Body or part of it.
     *
     * @param string|null $name
     * @param int|null $filter
     * @param array<int,int>|int $filterOptions
     *
     * @return array<int|string,mixed>|mixed|string|null
     */
    public function getParsedBody(
        string $name = null,
        int $filter = null,
        array | int $filterOptions = []
    ) {
        if ($this->getMethod() === 'POST') {
            return $this->getPOST($name, $filter, $filterOptions);
        }
        if ($this->parsedBody === null) {
            $this->isForm()
                ? \parse_str($this->getBody(), $this->parsedBody)
                : $this->parsedBody = [];
        }
        $variable = $name === null
            ? $this->parsedBody
            : ArraySimple::value($name, $this->parsedBody);
        return $filter
            ? \filter_var($variable, $filter, $filterOptions)
            : $variable;
    }

    /**
     * Get the request body as JSON.
     *
     * @param bool $assoc
     * @param int|null $options
     * @param int $depth
     *
     * @return array<string,mixed>|false|object
     */
    public function getJson(bool $assoc = false, int $options = null, int $depth = 512)
    {
        if ($options === null) {
            $options = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES;
        }
        $body = \json_decode($this->getBody(), $assoc, $depth, $options);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            return false;
        }
        return $body;
    }

    /**
     * @param string $type
     *
     * @return array<int,string>
     */
    protected function getNegotiableValues(string $type) : array
    {
        if ($this->negotiation[$type]) {
            return $this->negotiation[$type];
        }
        $this->negotiation[$type] = \array_keys(static::parseQualityValues(
            $this->getServer('HTTP_ACCEPT' . ($type !== 'ACCEPT' ? '_' . $type : ''))
        ));
        $this->negotiation[$type] = \array_map('strtolower', $this->negotiation[$type]);
        return $this->negotiation[$type];
    }

    /**
     * @param string $type
     * @param array<int,string> $negotiable
     *
     * @return string
     */
    protected function negotiate(string $type, array $negotiable) : string
    {
        $negotiable = \array_map('strtolower', $negotiable);
        foreach ($this->getNegotiableValues($type) as $item) {
            if (\in_array($item, $negotiable, true)) {
                return $item;
            }
        }
        return $negotiable[0];
    }

    /**
     * Get the mime types of the Accept header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept
     *
     * @return array<int,string>
     */
    public function getAccepts() : array
    {
        return $this->getNegotiableValues('ACCEPT');
    }

    /**
     * Negotiate the Accept header.
     *
     * @param array<int,string> $negotiable Allowed mime types
     *
     * @return string The negotiated mime type
     */
    public function negotiateAccept(array $negotiable) : string
    {
        return $this->negotiate('ACCEPT', $negotiable);
    }

    /**
     * Get the Accept-Charset's.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Charset
     *
     * @return array<int,string>
     */
    public function getCharsets() : array
    {
        return $this->getNegotiableValues('CHARSET');
    }

    /**
     * Negotiate the Accept-Charset.
     *
     * @param array<int,string> $negotiable Allowed charsets
     *
     * @return string The negotiated charset
     */
    public function negotiateCharset(array $negotiable) : string
    {
        return $this->negotiate('CHARSET', $negotiable);
    }

    /**
     * Get the Accept-Encoding.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Encoding
     *
     * @return array<int,string>
     */
    public function getEncodings() : array
    {
        return $this->getNegotiableValues('ENCODING');
    }

    /**
     * Negotiate the Accept-Encoding.
     *
     * @param array<int,string> $negotiable The allowed encodings
     *
     * @return string The negotiated encoding
     */
    public function negotiateEncoding(array $negotiable) : string
    {
        return $this->negotiate('ENCODING', $negotiable);
    }

    /**
     * Get the Accept-Language's.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
     *
     * @return array<int,string>
     */
    public function getLanguages() : array
    {
        return $this->getNegotiableValues('LANGUAGE');
    }

    /**
     * Negotiated the Accept-Language.
     *
     * @param array<int,string> $negotiable Allowed languages
     *
     * @return string The negotiated language
     */
    public function negotiateLanguage(array $negotiable) : string
    {
        return $this->negotiate('LANGUAGE', $negotiable);
    }

    /**
     * Get the Content-Type header value.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Type
     *
     * @return string|null
     */
    #[Pure]
    public function getContentType() : ?string
    {
        return $this->getHeader(static::HEADER_CONTENT_TYPE);
    }

    /**
     * @param string|null $name
     * @param int|null $filter
     * @param array<int,int>|int $filterOptions
     *
     * @return mixed
     */
    public function getEnv(
        string $name = null,
        int $filter = null,
        array | int $filterOptions = []
    ) : mixed {
        return $this->filterInput(\INPUT_ENV, $name, $filter, $filterOptions);
    }

    /**
     * @return array<string,array|UploadedFile>
     */
    #[Pure]
    public function getFiles() : array
    {
        return $this->files;
    }

    #[Pure]
    public function hasFiles() : bool
    {
        return ! empty($this->files);
    }

    public function getFile(string $name) : ?UploadedFile
    {
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
     * @return mixed
     */
    public function getGet(
        string $name = null,
        int $filter = null,
        array | int $filterOptions = []
    ) : mixed {
        return $this->filterInput(\INPUT_GET, $name, $filter, $filterOptions);
    }

    /**
     * @return string
     */
    #[Pure]
    public function getHost() : string
    {
        return $this->host;
    }

    /**
     * Get the X-Request-ID header.
     *
     * @return string|null
     */
    public function getId() : string | null
    {
        if (isset($this->id)) {
            return $this->id === false ? null : $this->id;
        }
        $this->id = $this->getHeader(static::HEADER_X_REQUEST_ID) ?? false;
        return $this->getId();
    }

    /**
     * Get the connection IP.
     *
     * @return string
     */
    public function getIp() : string
    {
        return $this->getServer('REMOTE_ADDR');
    }

    #[Pure]
    public function getMethod() : string
    {
        return parent::getMethod();
    }

    /**
     * Gets data from the last request, if it was redirected.
     *
     * @param string|null $key a key name or null to get all data
     *
     * @see Response::redirect
     *
     * @throws LogicException if PHP Session is not active to get redirect data
     *
     * @return mixed an array containing all data, the key value or null
     * if the key was not found
     */
    public function getRedirectData(string $key = null) : mixed
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
        return $this->port ?? $this->getServer('SERVER_PORT');
    }

    /**
     * Get POST data.
     *
     * @param string|null $name
     * @param int|null $filter
     * @param array<int,int>|int $filterOptions
     *
     * @return mixed
     */
    public function getPost(
        string $name = null,
        int $filter = null,
        array | int $filterOptions = []
    ) : mixed {
        return $this->filterInput(\INPUT_POST, $name, $filter, $filterOptions);
    }

    /**
     * Get the connection IP via a proxy header.
     *
     * @return string|null
     */
    #[Pure]
    public function getProxiedIp() : ?string
    {
        foreach ([
            'X-Forwarded-For',
            'Client-IP',
            'X-Client-IP',
            'X-Cluster-Client-IP',
        ] as $header) {
            $header = $this->getHeader($header);
            if ($header) {
                return $header;
            }
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
        if ( ! isset($this->referrer)) {
            $this->referrer = false;
            $referer = $this->getHeader(static::HEADER_REFERER);
            if ($referer !== null) {
                try {
                    $this->referrer = new URL($referer);
                } catch (InvalidArgumentException) {
                    $this->referrer = false;
                }
            }
        }
        return $this->referrer ?: null;
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
        string $name = null,
        int $filter = null,
        array | int $filterOptions = []
    ) : mixed {
        return $this->filterInput(\INPUT_SERVER, $name, $filter, $filterOptions);
    }

    /**
     * Gets the requested URL.
     *
     * @return URL
     */
    #[Pure]
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
        if (isset($this->userAgent) && $this->userAgent instanceof UserAgent) {
            return $this->userAgent;
        }
        $userAgent = $this->getHeader(static::HEADER_USER_AGENT);
        $userAgent ? $this->setUserAgent($userAgent) : $this->userAgent = false;
        return $this->userAgent ?: null;
    }

    /**
     * @param string|UserAgent $userAgent
     *
     * @return static
     */
    protected function setUserAgent(string | UserAgent $userAgent) : static
    {
        if ( ! $userAgent instanceof UserAgent) {
            $userAgent = new UserAgent($userAgent);
        }
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * Check if is an AJAX Request based in the X-Requested-With Header.
     *
     * The X-Requested-With Header containing the "XMLHttpRequest" value is
     * used by various javascript libraries.
     *
     * @return bool
     */
    public function isAjax() : bool
    {
        if (isset($this->isAjax)) {
            return $this->isAjax;
        }
        $received = $this->getHeader(static::HEADER_X_REQUESTED_WITH);
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
        return $this->isSecure = ($this->getServer('REQUEST_SCHEME') === 'https'
            || $this->getServer('HTTPS') === 'on');
    }

    /**
     * Say if the request is done with application/x-www-form-urlencoded
     * Content-Type.
     *
     * @return bool
     */
    #[Pure]
    public function isForm() : bool
    {
        return $this->parseContentType() === 'application/x-www-form-urlencoded';
    }

    /**
     * Say if the request is a JSON call.
     *
     * @return bool
     */
    #[Pure]
    public function isJson() : bool
    {
        return $this->parseContentType() === 'application/json';
    }

    /**
     * Say if the request method is POST.
     *
     * @return bool
     */
    #[Pure]
    public function isPost() : bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * @see https://www.sitepoint.com/community/t/-files-array-structure/2728/5
     *
     * @return array<string,array|UploadedFile>
     */
    protected function getInputFiles() : array
    {
        if (empty($_FILES)) {
            return [];
        }
        $make_objects = static function (
            array $array,
            callable $make_objects
        ) : array | UploadedFile {
            $return = [];
            foreach ($array as $k => $v) {
                if (\is_array($v)) {
                    $return[$k] = $make_objects($v, $make_objects);
                    continue;
                }
                return new UploadedFile($array);
            }
            return $return;
        };
        return $make_objects(ArraySimple::files(), $make_objects); // @phpstan-ignore-line
    }

    /**
     * @param string $host
     *
     * @throws InvalidArgumentException for invalid host
     *
     * @return static
     */
    protected function setHost(string $host) : static
    {
        $filtered_host = 'http://' . $host;
        $filtered_host = \filter_var($filtered_host, \FILTER_VALIDATE_URL);
        if ( ! $filtered_host) {
            throw new InvalidArgumentException("Invalid host: {$host}");
        }
        $host = \parse_url($filtered_host);
        $this->host = $host['host']; // @phpstan-ignore-line
        if (isset($host['port'])) { // @phpstan-ignore-line
            $this->port = $host['port'];
        }
        return $this;
    }
}
