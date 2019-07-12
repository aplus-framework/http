<?php namespace Framework\HTTP;

/**
 * Class Request.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#HTTP_Requests
 */
class Request extends Message implements RequestInterface
{
	/**
	 * HTTP Request Method.
	 *
	 * @var string
	 */
	protected $method;
	/**
	 * HTTP Request Server Variables.
	 *
	 * @var array
	 */
	protected $serverVariables = [];
	protected $post = [];
	protected $get = [];
	protected $env = [];
	protected $files = [];
	/**
	 * @var array|null
	 */
	protected $parsedBody;
	/**
	 * HTTP Authorization Header parsed.
	 *
	 * @var array|null
	 */
	protected $auth;
	/**
	 * @var string|null
	 */
	protected $authType;
	/**
	 * @var array|false|null
	 */
	protected $geoip;
	/**
	 * @var string
	 */
	protected $host;
	/**
	 * Request Identifier.
	 *
	 * @var string|null 32 bytes
	 */
	protected $id;
	/**
	 * @var array
	 */
	protected $negotiation = [
		'ACCEPT' => null,
		'CHARSET' => null,
		'ENCODING' => null,
		'LANGUAGE' => null,
	];
	/**
	 * @var int|null
	 */
	protected $port;
	/**
	 * @var false|URL
	 */
	protected $referrer;
	/**
	 * @var URL
	 */
	protected $url;
	/**
	 * @var UserAgent|null
	 */
	protected $userAgent;
	/**
	 * @var bool|null
	 */
	protected $isAJAX;
	/**
	 * @var bool|null
	 */
	protected $isSecure;

	/**
	 * Request constructor.
	 */
	public function __construct()
	{
		$this->prepareServerVariables();
		$this->prepareStatusLine();
		$this->prepareHeaders();
		$this->prepareCookies();
		$this->prepareUserAgent();
		$this->prepareBody();
		$this->preparePOST();
		$this->prepareGET();
		$this->prepareENV();
		$this->prepareFiles();
	}

	protected function filterInput(int $type) : array
	{
		return (array) \filter_input_array($type);
	}

	protected function prepareServerVariables()
	{
		foreach ($this->filterInput(\INPUT_SERVER) as $key => $value) {
			$this->serverVariables[$key] = $value;
		}
		\ksort($this->serverVariables);
	}

	protected function prepareStatusLine()
	{
		$this->setProtocol($this->getServerVariable('SERVER_PROTOCOL'));
		$this->setMethod($this->getServerVariable('REQUEST_METHOD'));
		$url = $this->isSecure() ? 'https' : 'http';
		$url .= '://' . $this->getServerVariable('HTTP_HOST');
		//$url .= ':' . $this->getPort();
		$url .= $this->getServerVariable('REQUEST_URI');
		$this->setURL($url);
		$this->setHost($this->getURL()->getHost());
	}

	protected function prepareHeaders()
	{
		foreach ($this->getServerVariables() as $name => $value) {
			if (\strpos($name, 'HTTP_') !== 0) {
				continue;
			}
			$name = \strtr(\substr($name, 5), ['_' => '-']);
			$this->setHeader($name, $value);
		}
	}

	protected function prepareCookies()
	{
		foreach ($this->filterInput(\INPUT_COOKIE) as $name => $value) {
			$this->setCookie(new Cookie($name, $value));
		}
	}

	protected function prepareUserAgent()
	{
		$userAgent = $this->getServerVariable('HTTP_USER_AGENT');
		if ($userAgent) {
			$this->setUserAgent($userAgent);
		}
	}

	protected function prepareBody()
	{
		$this->body = \file_get_contents('php://input') ?: '';
	}

	protected function preparePOST()
	{
		$this->post = $this->filterInput(\INPUT_POST);
	}

	protected function prepareGET()
	{
		$this->get = $this->filterInput(\INPUT_GET);
	}

	protected function prepareENV()
	{
		$this->env = $this->filterInput(\INPUT_ENV);
	}

	protected function prepareFiles()
	{
		$this->files = $this->getInputFiles();
	}

	/**
	 * @param mixed          $variable
	 * @param int|null       $filter
	 * @param array|int|null $options
	 *
	 * @return mixed
	 */
	protected function filter($variable, int $filter = null, $options = null)
	{
		return $filter
			? \filter_var($variable, $filter, $options)
			: $variable;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function forceHTTPS() : void
	{
		if ( ! $this->isSecure()) {
			\header('Location: ' . $this->getURL()->setScheme('https')->getURL(), 301);
			exit;
		}
	}

	/*public function getBestLanguage(): string
	{
		return $this->getLanguage(Services::language()->getSupportedLocales());
	}*/
	public function getAuthType() : ?string
	{
		if ($this->authType === null && $auth = $this->getHeader('Authorization')) {
			$this->parseAuth($auth);
		}
		return $this->authType;
	}

	public function getBasicAuth() : ?array
	{
		return $this->getAuthType() === 'Basic'
			? $this->auth
			: null;
	}

	public function getDigestAuth() : ?array
	{
		return $this->getAuthType() === 'Digest'
			? $this->auth
			: null;
	}

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
		return $data;
	}

	public function getParsedBody() : array
	{
		if ($this->parsedBody === null) {
			\parse_str($this->getBody(), $this->parsedBody);
			return $this->parsedBody;
		}
		return $this->parsedBody;
	}

	/**
	 * @param string $type
	 *
	 * @return array
	 */
	protected function getNegotiableValues(string $type) : array
	{
		if ($this->negotiation[$type]) {
			return $this->negotiation[$type];
		}
		$this->negotiation[$type] = \array_keys($this->parseQualityValues(
			$this->getServerVariable('HTTP_ACCEPT' . ($type !== 'ACCEPT' ? '_' . $type : ''))
		));
		$this->negotiation[$type] = \array_map('strtolower', $this->negotiation[$type]);
		return $this->negotiation[$type];
	}

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

	public function getAccepts() : array
	{
		return $this->getNegotiableValues('ACCEPT');
	}

	public function negotiateAccept(array $negotiable) : string
	{
		return $this->negotiate('ACCEPT', $negotiable);
	}

	public function getCharsets() : array
	{
		return $this->getNegotiableValues('CHARSET');
	}

	public function negotiateCharset(array $negotiable) : string
	{
		return $this->negotiate('CHARSET', $negotiable);
	}

	public function getEncodings() : array
	{
		return $this->getNegotiableValues('ENCODING');
	}

	public function negotiateEncoding(array $negotiable) : string
	{
		return $this->negotiate('ENCODING', $negotiable);
	}

	public function getLanguages() : array
	{
		return $this->getNegotiableValues('LANGUAGE');
	}

	public function negotiateLanguage(array $negotiable) : string
	{
		return $this->negotiate('LANGUAGE', $negotiable);
	}

	/**
	 * @return string|null
	 */
	public function getContentType() : ?string
	{
		return $this->getServerVariable('HTTP_CONTENT_TYPE');
	}

	public function getCSRFToken() : ?string
	{
		return ($cookie = $this->getCookie('X-CSRF-Token')) ? $cookie->getValue() : null;
	}

	public function validateCSRFToken(string $token) : bool
	{
		return $this->getCSRFToken() === $token;
	}

	/**
	 * @param array|string|null $name
	 * @param int|null          $filter
	 * @param array|int|null    $filter_options
	 *
	 * @return array|mixed|null
	 */
	public function getENV($name = null, int $filter = null, $filter_options = null)
	{
		return $this->filter(
			$name === null ? $this->env : \ArraySimple::value($name, $this->env[$name]),
			$filter,
			$filter_options
		);
	}

	/**
	 * @return string|null
	 */
	public function getETag() : ?string
	{
		return $this->getServerVariable('HTTP_ETAG');
	}

	/**
	 * @return array|UploadedFile[]
	 */
	public function getFiles() : array
	{
		return $this->files;
	}

	public function getFile(string $name) : ?UploadedFile
	{
		return \ArraySimple::value($name, $this->files);
	}

	/**
	 * @return GeoIP
	 */
	public function getGeoIP() : GeoIP
	{
		if ($this->geoip === null) {
			$this->geoip = new GeoIP($this->getIP());
		}
		return $this->geoip;
	}

	public function getQuery(string $name) : ?string
	{
		return \ArraySimple::value($name, $this->getQueries());
	}

	public function getQueries() : array
	{
		return $this->get;
	}

	/**
	 * @return string
	 */
	public function getHost() : string
	{
		return $this->host;
	}

	/**
	 * Get the Request ID.
	 *
	 * @return string
	 */
	public function getId() : string
	{
		if ($this->id !== null) {
			return $this->id;
		}
		$this->id = $this->getServerVariable('HTTP_X_REQUEST_ID');
		if (empty($this->id)) {
			$this->id = \md5(\uniqid($this->getIP(), true));
		}
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getIP() : string
	{
		return $this->getServerVariable('REMOTE_ADDR');
	}

	/**
	 * @param bool $assoc
	 * @param int  $options
	 * @param int  $depth
	 *
	 * @return array|false|object
	 */
	public function getJSON(bool $assoc = false, int $options = null, int $depth = 512)
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
	 * Gets the HTTP method.
	 *
	 * @return string normally one of: GET, HEAD, POST, PATCH, PUT, DELETE or OPTIONS
	 */
	public function getMethod() : string
	{
		return $this->method;
	}

	protected function setMethod(string $method)
	{
		$method = \strtoupper($method);
		if ( ! \in_array($method, [
			'GET',
			'HEAD',
			'POST',
			'PATCH',
			'PUT',
			'DELETE',
			'OPTIONS',
		], true)) {
			throw new \InvalidArgumentException("Invalid HTTP method: {$method}");
		}
		$this->method = $method;
		return $this;
	}

	/**
	 * Gets data from the last request, if it was redirected.
	 *
	 * @param string $key a key name or null to get all data
	 *
	 * @see Response::redirect
	 *
	 * @throws \LogicException if PHP Session is not active to get redirect data
	 *
	 * @return mixed|null an array containing all data, the key value or null if the key was not
	 *                    found
	 */
	public function getRedirectData(string $key = null)
	{
		static $data;
		if ($data === null && \session_status() !== \PHP_SESSION_ACTIVE) {
			throw new \LogicException('Session must be active to get redirect data');
		}
		if ($data === null) {
			$data = $_SESSION['$__REDIRECT'] ?? false;
			unset($_SESSION['$__REDIRECT']);
		}
		if ($key !== null && $data) {
			return \ArraySimple::value($key, $data);
		}
		return $data ?: null;
	}

	/**
	 * @return int
	 */
	public function getPort() : int
	{
		return $this->port ?? $this->getServerVariable('SERVER_PORT');
	}

	/**
	 * @param array|string|null $name
	 * @param int|null          $filter
	 * @param array|int|null    $filter_options
	 *
	 * @return array|string|null
	 */
	public function getPOST($name = null, int $filter = null, $filter_options = null)
	{
		return $this->filter(
			$name === null ? $this->post : \ArraySimple::value($name, $this->post),
			$filter,
			$filter_options
		);
	}

	/**
	 * @return string|null
	 */
	public function getProxiedIP() : ?string
	{
		foreach ([
			'HTTP_X_FORWARDED_FOR',
			'HTTP_CLIENT_IP',
			'HTTP_X_CLIENT_IP',
			'HTTP_X_CLUSTER_CLIENT_IP',
		] as $header) {
			if ($header = $this->getServerVariable($header)) {
				return $header;
			}
		}
		return null;
	}

	/**
	 * @return URL|null
	 */
	public function getReferer() : ?URL
	{
		if ($this->referrer === null) {
			$this->referrer = false;
			$referer = $this->getServerVariable('HTTP_REFERER');
			if ($referer) {
				try {
					$this->referrer = new URL($referer);
				} catch (\InvalidArgumentException $e) {
					$this->referrer = false;
				}
			}
		}
		return $this->referrer ?: null;
	}

	public function getServerVariable(string $name) : ?string
	{
		return \ArraySimple::value($name, $this->serverVariables);
	}

	public function getServerVariables()
	{
		return $this->serverVariables;
	}

	/**
	 * Gets the requested URL.
	 *
	 * @return URL
	 */
	public function getURL() : URL
	{
		return $this->url;
	}

	/**
	 * @param string|URL $url
	 *
	 * @return $this
	 */
	protected function setURL($url)
	{
		if ( ! $url instanceof URL) {
			$url = new URL($url);
		}
		$this->url = $url;
		return $this;
	}

	/**
	 * Gets the User Agent client.
	 *
	 * @return UserAgent|null the UserAgent object or null if no
	 *                        user-agent header was received
	 */
	public function getUserAgent() : ?UserAgent
	{
		return $this->userAgent;
	}

	/**
	 * @param string|UserAgent $user_agent
	 *
	 * @return $this
	 */
	protected function setUserAgent($user_agent)
	{
		if ( ! $user_agent instanceof UserAgent) {
			$user_agent = new UserAgent($user_agent);
		}
		$this->userAgent = $user_agent;
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
	public function isAJAX() : bool
	{
		if ($this->isAJAX !== null) {
			return $this->isAJAX;
		}
		$received = $this->getServerVariable('HTTP_X_REQUESTED_WITH');
		return $this->isAJAX = $received
			? \strtolower($received) === 'xmlhttprequest'
			: false;
	}

	/**
	 * @return bool
	 */
	public function isSecure() : bool
	{
		if ($this->isSecure !== null) {
			return $this->isSecure;
		}
		return $this->isSecure = ($this->getServerVariable('REQUEST_SCHEME') === 'https'
			|| $this->getServerVariable('HTTPS') === 'on');
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Glossary/Quality_values
	 * @see https://stackoverflow.com/a/33748742/6027968
	 *
	 * @param string|null $string
	 *
	 * @return array
	 */
	protected function parseQualityValues(?string $string) : array
	{
		if (empty($string)) {
			return [];
		}
		$quality = \array_reduce(
			\explode(',', $string, 20),
			static function ($qualifier, $part) {
				[$value, $priority] = \array_merge(\explode(';q=', $part), [1]);
				$qualifier[\trim($value)] = (float) $priority;
				return $qualifier;
			},
			[]
		);
		\arsort($quality);
		return $quality;
	}

	protected function getInputFiles() : array
	{
		if (empty($_FILES)) {
			return [];
		}
		// See: https://stackoverflow.com/a/33261775/6027968
		$walker = static function ($array, $fileInfokey, callable $walker) {
			$return = [];
			foreach ($array as $k => $v) {
				if (\is_array($v)) {
					$return[$k] = $walker($v, $fileInfokey, $walker);
					continue;
				}
				$return[$k][$fileInfokey] = $v;
			}
			return $return;
		};
		$files = [];
		foreach ($_FILES as $name => $values) {
			// init for array_merge
			if ( ! isset($files[$name])) {
				$files[$name] = [];
			}
			if ( ! \is_array($values['error'])) {
				// normal syntax
				$files[$name] = $values;
				continue;
			}
			// html array feature
			foreach ($values as $fileInfoKey => $subArray) {
				$files[$name] = \array_replace_recursive(
					$files[$name],
					$walker($subArray, $fileInfoKey, $walker)
				);
			}
		}
		// See: https://www.sitepoint.com/community/t/-files-array-structure/2728/5
		$make_objects = static function ($array, callable $make_objects) {
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
		return $make_objects($files, $make_objects);
	}

	/**
	 * @param string $host
	 *
	 * @return $this
	 */
	protected function setHost(string $host)
	{
		$filtered_host = 'http://' . $host;
		if ( ! $filtered_host = $this->filter($filtered_host, \FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException("Invalid host: {$host}");
		}
		$host = \parse_url($filtered_host);
		$this->host = $host['host'];
		if (isset($host['port'])) {
			$this->port = $host['port'];
		}
		return $this;
	}
}
