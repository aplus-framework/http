<?php namespace Framework\HTTP;

use Framework\HTTP\Exceptions\RequestException;

/**
 * Class Request.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#HTTP_Requests
 */
class Request extends Message //implements RequestInterface
{
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
	 * @var false|\Framework\HTTP\URL
	 */
	protected $referrer;
	/**
	 * @var \Framework\HTTP\URL
	 */
	protected $url;
	/**
	 * @var \Framework\HTTP\UserAgent
	 */
	protected $userAgent;

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
			\header('Location: ' . $this->getURL(true)->setScheme('https')->getURL(), 301);
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
		return $this->negotiable('ACCEPT', $negotiable);
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

	/**
	 * @param bool $parse
	 *
	 * @return array|string
	 *
	 * @codeCoverageIgnore
	 */
	public function getBody(bool $parse = false)
	{
		if ($parse) {
			\parse_str($this->getBody(), $parse);
			return $parse;
		}
		if ($this->body === null) {
			$this->body = \file_get_contents('php://input') ?: '';
		}
		return $this->body;
	}

	/**
	 * @param array $negotiable
	 *
	 * @return array|string
	 */
	public function getCharset(array $negotiable = [])
	{
		return $this->negotiable('CHARSET', $negotiable);
	}

	/**
	 * @return string|null
	 */
	public function getContentType() : ?string
	{
		return $this->getServer('HTTP_CONTENT_TYPE');
	}

	/**
	 * @param string|string[]|null $name
	 * @param int|null             $filter
	 * @param array|int|null       $filter_options
	 *
	 * @return string|string[]|null
	 */
	public function getCookie($name = null, int $filter = null, $filter_options = null)
	{
		return $this->getReturn($name, 'COOKIE', $filter, $filter_options);
	}

	public function getCSRFToken() : ?string
	{
		return $this->getCookie('X-CSRF-Token');
	}

	/**
	 * @param array $negotiable
	 *
	 * @return array|string
	 */
	public function getEncoding(array $negotiable = [])
	{
		return $this->negotiable('ENCODING', $negotiable);
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
	 * @param string|null $name Input form name
	 *
	 * @return array|\Framework\HTTP\UploadedFile|\Framework\HTTP\UploadedFile[]|null
	 */
	public function getFiles(string $name = null)
	{
		if ($this->input['FILES'] === null) {
			$this->input['FILES'] = $this->prepareFiles();
		}
		return $name === null
			? $this->input['FILES']
			//: \array_simple_value($name, $this->input['FILES']);
			: $this->input['FILES'][$name] ?? null;
	}

	/**
	 * @param string|null $custom_directory
	 *
	 * @return \Framework\HTTP\GeoIP
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
	 * @param null           $name
	 * @param int|null       $filter
	 * @param array|int|null $filter_options
	 *
	 * @return array|string|null
	 */
	public function getHeader($name = null, int $filter = null, $filter_options = null)
	{
		// Populate the HEADERS by the first time
		if ($this->input['HEADERS'] === null) {
			$headers = [];
			foreach ($this->getServer() as $key => $value) {
				if (\substr($key, 0, 5) !== 'HTTP_') {
					continue;
				}
				$key = \strtr(\substr($key, 5), '_', '-');
				$headers[$this->getHeaderName($key)] = (string) $value;
			}
			$this->input['HEADERS'] = $headers;
		}
		if (\is_array($name)) {
			foreach ($name as &$n) {
				$n = $this->getHeaderName($n);
			}
		} elseif ($name !== null) {
			$name = $this->getHeaderName($name);
		}
		return $this->getReturn($name, 'HEADERS', $filter, $filter_options);
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
				$type = \INPUT_POST;
				break;
			case 'GET':
				$type = \INPUT_GET;
				break;
			case 'COOKIE':
				$type = \INPUT_COOKIE;
				break;
			case 'ENV':
				$type = \INPUT_ENV;
				break;
			case 'SERVER':
				$type = \INPUT_SERVER;
				break;
			default:
				throw RequestException::forInvalidInputType($type);
		}
		$type = (array) \filter_input_array($type);
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
	 * @return array|string
	 */
	public function getLanguage(array $negotiable = [])
	{
		return $this->negotiable('LANGUAGE', $negotiable);
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
	 * @param bool $parse
	 *
	 * @return \Framework\HTTP\URL|string|null
	 */
	public function getReferrer(bool $parse = false)
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
		if ($this->referrer) {
			return $parse ? $this->referrer : $this->referrer->getURL();
		}
		return null;
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
				//\array_simple_value($n, $this->input[$type]),
					$this->input[$type][$n] ?? null,
					$filter,
					$filter_options
				);
			}
			return $data;
		}
		return $this->filter(
		//\array_simple_value($name, $this->input[$type]),
			$this->input[$type][$name] ?? null,
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
	 * @param bool $as_object set TRUE to returns the URL object instance
	 *
	 * @return \Framework\HTTP\URL|string the URL string or the URL object
	 */
	public function getURL(bool $as_object = false)
	{
		if ($this->url) {
			return $as_object ? $this->url : $this->url->getURL();
		}
		$url = $this->isSecure() ? 'https' : 'http';
		$url .= '://' . $this->getHost();
		$url .= ':' . $this->getPort();
		$url .= $this->getServer('REQUEST_URI');
		//\var_dump($url);exit;
		$this->url = new URL($url);
		return $as_object ? $this->url : $this->url->getURL();
	}

	/**
	 * Gets the User Agent client.
	 *
	 * @param bool  $as_object set TRUE to returns the UserAgent object instance
	 * @param array $config    Extra configurations passed to the UserAgent object: platforms,
	 *                         browsers, mobiles, robots
	 *
	 * @return \Framework\HTTP\UserAgent|string|null the User Agent string, UserAgent object or
	 *                                               null if no user-agent header was received
	 */
	public function getUserAgent(bool $as_object = false, array $config = [])
	{
		$user_agent = $this->getServer('HTTP_USER_AGENT');
		if ($user_agent === null) {
			return null;
		}
		if ($as_object) {
			return $this->userAgent
				?? $this->userAgent = new UserAgent($user_agent, $config);
		}
		return $user_agent;
	}

	/**
	 * Check if is an AJAX Request based in the X-Requested-With Header.
	 *
	 * The X-Requested-With Header containing the "XMLHttpRequest" value is
	 * used by various javascript libraries.
	 *
	 * You can to set a custom X-Requested-With Header if needed.
	 *
	 * @param bool   $lowercase True to compare with lowercase, false otherwise
	 * @param string $header    X-Requested-With Header Value
	 *
	 * @return bool
	 */
	public function isAJAX(bool $lowercase = true, string $header = 'XmlHttpRequest') : bool
	{
		$received = $this->getServer('HTTP_X_REQUESTED_WITH');
		if ($received === null) {
			return false;
		}
		if ($lowercase) {
			return \strtolower($received) === \strtolower($header);
		}
		return $received === $header;
	}

	/**
	 * @return bool
	 */
	public function isSecure() : bool
	{
		return $this->getServer('REQUEST_SCHEME') === 'https'
			|| $this->getServer('HTTPS') === 'on';
	}

	/**
	 * @param string $type
	 * @param array  $negotiable
	 *
	 * @return array|string
	 */
	protected function negotiable(string $type, array $negotiable = [])
	{
		if ($this->input[$type] === null) {
			$this->input[$type] = \array_keys($this->parseQValues(
				$this->getServer('HTTP_ACCEPT' . ($type !== 'ACCEPT' ? '_' . $type : ''))
			));
			$this->input[$type] = \array_map('strtolower', $this->input[$type]);
		}
		if ($negotiable) {
			$negotiable = \array_map('strtolower', $negotiable);
			foreach ($this->input[$type] as $item) {
				if (\in_array($item, $negotiable, true)) {
					return $item;
				}
			}
			return $negotiable[0];
		}
		return $this->input[$type];
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
		$quality = \array_reduce(\explode(',', $string, 20), function ($q, $part) {
			[$value, $priority] = \array_merge(\explode(';q=', $part), [1]);
			$q[\trim($value)] = (float) $priority;
			return $q;
		}, []);
		\arsort($quality);
		return $quality;
	}

	protected function prepareFiles() : array
	{
		if (empty($_FILES)) {
			return [];
		}
		// See: https://stackoverflow.com/a/33261775/6027968
		$walker = function ($array, $fileInfokey, callable $walker) {
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
		$make_objects = function ($array, callable $make_objects) {
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
			throw RequestException::forInvalidHost($host);
		}
		$host = \parse_url($filtered_host);
		$this->host = $host['host'];
		if (isset($host['port'])) {
			$this->port = $host['port'];
		}
		return $this;
	}
}
