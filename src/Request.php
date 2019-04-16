<?php namespace Framework\HTTP;

/**
 * Class Request.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#HTTP_Requests
 */
class Request extends Message //implements RequestInterface
{
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
	protected $input = [
		'POST' => null,
		'GET' => null,
		'COOKIE' => null,
		'ENV' => null,
		'SERVER' => null,
		// Custom
		'HEADERS' => null,
		'FILES' => null,
		// Content Negotiation
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
	 *
	 * @param string|null $host
	 */
	public function __construct(string $host = null)
	{
		if ($host === null) {
			$host = $this->getServer('HTTP_HOST') ?? $this->getServer('SERVER_NAME');
		}
		$this->setHost($host);
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

	/**
	 * @param array $negotiable
	 *
	 * @return array|string
	 */
	public function getAccept(array $negotiable = [])
	{
		return $this->negotiate('ACCEPT', $negotiable);
	}

	public function getBasicAuth() : ?array
	{
		if ($this->authType === null) {
			$this->parseAuth();
		}
		if ($this->authType === 'basic') {
			$this->auth;
		}
		return null;
	}

	/**
	 * Get Authorization Header data.
	 *
	 * @return array
	 */
	public function getAuth() : array
	{
		if ($this->auth !== null) {
			return $this->auth;
		}
		$auth = $this->getHeader('Authorization');
		if ($auth) {
			[$type, $attributes] = \array_pad(\explode(' ', $auth, 2), 2, null);
			if ($type === 'Basic' && $attributes) {
				$data = [
					'type' => $type,
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
			} elseif ($type === 'Digest' && $attributes) {
				$data = [
					'type' => $type,
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
			}
		}
		return $this->auth = $data ?? [];
	}

	protected function parseAuth() : ?array
	{
		$this->authType = false;
		$auth = $this->getHeader('Authorization');
		if ($auth) {
			[$type, $attributes] = \array_pad(\explode(' ', $auth, 2), 2, null);
			if ($type === 'Basic') {
				$this->authType = 'basic';
				$this->auth = $this->parseBasicAuth($attributes);
			}
			if ($type === 'Digest') {
				$this->authType = 'digest';
				$this->auth = $this->parseDigestAuth($attributes);
			}
		}
		$this->auth = [];
	}

	protected function parseBasicAuth(string $attributes)
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

	protected function parseDigestAuth(string $attributes)
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

	public function getBody() : string
	{
		if ($this->body === null) {
			$this->body = \file_get_contents('php://input') ?: '';
		}
		return $this->body;
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
	 * @param array $negotiable
	 *
	 * @return string
	 */
	public function negotiateCharset(array $negotiable) : string
	{
		return $this->negotiate('CHARSET', $negotiable);
	}

	public function getCharsets() : array
	{
		return $this->negotiate('CHARSET');
	}

	/**
	 * @return string|null
	 */
	public function getContentType() : ?string
	{
		return $this->getServer('HTTP_CONTENT_TYPE');
	}

	/**
	 * @param string         $name
	 * @param int|null       $filter
	 * @param array|int|null $filter_options
	 *
	 * @return string|null
	 */
	public function getCookie(string $name, int $filter = null, $filter_options = null) : ?string
	{
		return $this->getReturn($name, 'COOKIE', $filter, $filter_options);
	}

	/**
	 * @return array|string[]
	 */
	public function getCookies() : array
	{
		return $this->getReturn(null, 'COOKIE');
	}

	public function getCSRFToken() : ?string
	{
		return $this->getCookie('X-CSRF-Token');
	}

	/**
	 * @param array $negotiable
	 *
	 * @return string
	 */
	public function negotiateEncoding(array $negotiable) : string
	{
		return $this->negotiate('ENCODING', $negotiable);
	}

	public function getEncodings() : array
	{
		return $this->negotiate('ENCODING');
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
		return $this->getReturn($name, 'ENV', $filter, $filter_options);
	}

	/**
	 * @return string|null
	 */
	public function getETag() : ?string
	{
		return $this->getServer('HTTP_ETAG');
	}

	/**
	 * @return array|UploadedFile[]
	 */
	public function getFiles() : array
	{
		return $this->getReturn(null, 'FILES');
	}

	public function getFile(string $name) : ?UploadedFile
	{
		return $this->getReturn($name, 'FILES');
	}

	/**
	 * @param string|null $custom_directory
	 *
	 * @return GeoIP
	 */
	public function getGeoIP(string $custom_directory = null) : GeoIP
	{
		if ($this->geoip === null) {
			// TODO: Remove this testback
			$ip = $this->getIP();
			//$ip = '170.82.196.47';
			$ip = '192.30.252.129';
			$this->geoip = new GeoIP($ip, $custom_directory);
		}
		return $this->geoip;
	}

	/**
	 * @param array|string|null $name
	 * @param int|null          $filter
	 * @param array|int|null    $filter_options
	 *
	 * @return array|string|null
	 */
	public function getGET($name = null, int $filter = null, $filter_options = null)
	{
		return $this->getReturn($name, 'GET', $filter, $filter_options);
	}

	/**
	 * @param string         $name
	 * @param int|null       $filter
	 * @param array|int|null $filter_options
	 *
	 * @return array|string|null
	 */
	public function getHeader(string $name, int $filter = null, $filter_options = null) : ?string
	{
		return $this->getReturn($this->getHeaderName($name), 'HEADERS', $filter, $filter_options);
	}

	public function getHeaders() : array
	{
		return $this->getReturn(null, 'HEADERS');
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
		// TODO: Use id in Debug collectors tabs - view/database
		// TODO: Try other Server keys - apache is different
		$this->id = $this->getServer('HTTP_X_REQUEST_ID');
		if (empty($this->id)) {
			$this->id = \md5(\uniqid($this->getIP(), true));
		}
		return $this->id;
	}

	/**
	 * @param string $type
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore
	 */
	protected function getInput(string $type) : array
	{
		switch ($type) {
			case 'POST':
				$type = (array) \filter_input_array(\INPUT_POST);
				break;
			case 'GET':
				$type = (array) \filter_input_array(\INPUT_GET);
				break;
			case 'COOKIE':
				$type = (array) \filter_input_array(\INPUT_COOKIE);
				break;
			case 'ENV':
				$type = (array) \filter_input_array(\INPUT_ENV);
				break;
			case 'SERVER':
				$type = (array) \filter_input_array(\INPUT_SERVER);
				break;
			case 'HEADERS':
				$type = $this->getInputHeaders();
				break;
			case 'FILES':
				$type = $this->getInputFiles();
				break;
			default:
				throw new \InvalidArgumentException("Invalid input type: {$type}");
		}
		\ksort($type);
		return $type;
	}

	/**
	 * @return string
	 */
	public function getIP() : string
	{
		return $this->getServer('REMOTE_ADDR');
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
	 * @param array $negotiable
	 *
	 * @return string
	 */
	public function negotiateLanguage(array $negotiable) : string
	{
		return $this->negotiate('LANGUAGE', $negotiable);
	}

	public function getLanguages() : array
	{
		return $this->negotiate('LANGUAGE');
	}

	/**
	 * Gets the HTTP method.
	 *
	 * @return string normally one of: GET, HEAD, POST, PATCH, PUT, DELETE or OPTIONS
	 */
	public function getMethod() : string
	{
		return $this->getServer('REQUEST_METHOD');
	}

	/**
	 * Gets data from the last request, if it was redirected.
	 *
	 * @param string $key a key name or null to get all data
	 *
	 * @return mixed|null an array containing all data, the key value or null if the key was not
	 *                    found
	 */
	public function getOld(string $key = null)
	{
		static $old;
		if ($old === null) {
			$old = (array) session()->getFlash('$__REDIRECT');
		}
		if ($key !== null && $old) {
			//return \array_simple_value($key, $old);
			return $old[$key] ?? null;
		}
		return $old;
	}

	/**
	 * @return int
	 */
	public function getPort() : int
	{
		return $this->port ?? $this->getServer('SERVER_PORT');
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
		return $this->getReturn($name, 'POST', $filter, $filter_options);
	}

	public function getProtocol() : string
	{
		return $this->getServer('SERVER_PROTOCOL') ?? 'HTTP/1.1';
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
			if ($header = $this->getServer($header)) {
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
			$referer = $this->getServer('HTTP_REFERER');
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

	protected function getInputHeaders() : array
	{
		$headers = [];
		foreach ($this->getServer() as $key => $value) {
			if (\strpos($key, 'HTTP_') !== 0) {
				continue;
			}
			$key = \strtr(\substr($key, 5), ['_' => '-']);
			$headers[$this->getHeaderName($key)] = (string) $value;
		}
		return $headers;
	}

	/**
	 * @param array|string|null $name
	 * @param string            $type
	 * @param int|null          $filter
	 * @param array|int|null    $filter_options
	 *
	 * @return array|mixed|null
	 */
	protected function getReturn($name, string $type, int $filter = null, $filter_options = null)
	{
		// Populate the input TYPE by the first time
		if ($this->input[$type] === null) {
			$this->input[$type] = $this->getInput($type);
		}
		if ($name === null) {
			return $this->filter($this->input[$type], $filter, $filter_options);
		}
		if (\is_array($name)) {
			$data = [];
			foreach ($name as $n) {
				$data[$n] = $this->filter(
					\ArraySimple::value($n, $this->input[$type]),
					$filter,
					$filter_options
				);
			}
			return $data;
		}
		return $this->filter(
			\ArraySimple::value($name, $this->input[$type]),
			$filter,
			$filter_options
		);
	}

	private function getScheme(string $host) : string
	{
		$scheme = \substr($host, 0, 7);
		if ($scheme === 'http://' || $scheme === 'https:/') {
			return '';
		}
		return 'http://';
	}

	/**
	 * @param array|string|null $name
	 * @param int|null          $filter
	 * @param array|int|null    $filter_options
	 *
	 * @return array|mixed|null
	 */
	public function getServer($name = null, int $filter = null, $filter_options = null)
	{
		return $this->getReturn($name, 'SERVER', $filter, $filter_options);
	}

	/**
	 * Gets the requested URL.
	 *
	 * @return URL
	 */
	public function getURL() : URL
	{
		if ($this->url) {
			return $this->url;
		}
		$url = $this->isSecure() ? 'https' : 'http';
		$url .= '://' . $this->getHost();
		$url .= ':' . $this->getPort();
		$url .= $this->getServer('REQUEST_URI');
		return $this->url = new URL($url);
	}

	/**
	 * Gets the User Agent client.
	 *
	 * @return UserAgent|null the UserAgent object or null if no
	 *                        user-agent header was received
	 */
	public function getUserAgent() : ?UserAgent
	{
		if ($this->userAgent) {
			return $this->userAgent;
		}
		$user_agent = $this->getServer('HTTP_USER_AGENT');
		if ($user_agent === null) {
			return null;
		}
		return $this->userAgent = new UserAgent($user_agent);
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
		$received = $this->getServer('HTTP_X_REQUESTED_WITH');
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
		return $this->isSecure = ($this->getServer('REQUEST_SCHEME') === 'https'
			|| $this->getServer('HTTPS') === 'on');
	}

	/**
	 * @param string $type
	 * @param array  $negotiable
	 *
	 * @return array|string
	 */
	protected function negotiate(string $type, array $negotiable = [])
	{
		if ($this->input[$type] === null) {
			$this->input[$type] = \array_keys($this->parseQValues(
				$this->getServer('HTTP_ACCEPT' . ($type !== 'ACCEPT' ? '_' . $type : ''))
			));
			$this->input[$type] = \array_map('strtolower', $this->input[$type]);
		}
		if (empty($negotiable)) {
			return $this->input[$type];
		}
		$negotiable = \array_map('strtolower', $negotiable);
		foreach ($this->input[$type] as $item) {
			if (\in_array($item, $negotiable, true)) {
				return $item;
			}
		}
		return $negotiable[0];
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Glossary/Quality_values
	 * @see https://stackoverflow.com/a/33748742/6027968
	 *
	 * @too Atualizar . tem uma versão mais no na antigo pacote
	 *
	 * @param string|null $string
	 *
	 * @return array
	 */
	protected function parseQValues(?string $string) : array
	{
		if (empty($string)) {
			return [];
		}
		$quality = \array_reduce(\explode(',', $string, 20), static function ($q, $part) {
			[$value, $priority] = \array_merge(\explode(';q=', $part), [1]);
			$q[\trim($value)] = (float) $priority;
			return $q;
		}, []);
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
				} else {
					$return[$k][$fileInfokey] = $v;
				}
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
			} else {
				// html array feature
				foreach ($values as $fileInfoKey => $subArray) {
					$files[$name] = \array_replace_recursive(
						$files[$name],
						$walker($subArray, $fileInfoKey, $walker)
					);
				}
			}
		}
		// See: https://www.sitepoint.com/community/t/-files-array-structure/2728/5
		$make_objects = static function ($array, callable $make_objects) {
			$return = [];
			foreach ($array as $k => $v) {
				if (\is_array($v)) {
					$return[$k] = $make_objects($v, $make_objects);
				} else {
					return new UploadedFile($array);
				}
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
		$filtered_host = $this->getScheme($host) . $host;
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
