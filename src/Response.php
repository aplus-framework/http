<?php namespace Framework\HTTP;

/**
 * Class Response.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#HTTP_Responses
 */
class Response extends Message //implements ResponseInterface
{
	/**
	 * @var int
	 */
	protected $cacheSeconds = 0;
	/**
	 * @var array
	 */
	protected $input = [
		'SERVER' => null,
	];
	/**
	 * @var bool
	 */
	protected $isSent = false;
	/**
	 * @var Request
	 */
	protected $request;
	/**
	 * Standard Response Codes.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
	 *
	 * @var array
	 */
	protected $responseCodes = [
		// Information responses
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		103 => 'Early Hints',
		// Successful responses
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
		// Redirection messages
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		// Client errors responses
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
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => "I'm a teapot",
		421 => 'Misdirected Request',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		451 => 'Unavailable For Legal Reasons',
		499 => 'Client Closed Request',
		// Server error responses
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
	 * HTTP Status Code.
	 *
	 * @var int
	 */
	protected $statusCode = 200;
	/**
	 * HTTP Status Reason.
	 *
	 * @var string
	 */
	protected $statusReason = 'OK';

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * @return string
	 */
	public function getBody() : string
	{
		$buffer = '';
		if (\ob_get_length()) {
			$buffer = \ob_get_contents();
			\ob_clean();
		}
		return $this->body .= $buffer;
	}

	/**
	 * @param string $body
	 *
	 * @return $this
	 */
	public function setBody(string $body)
	{
		if (\ob_get_length()) {
			\ob_clean();
		}
		$this->body = $body;
		return $this;
	}

	public function prependBody(string $content)
	{
		$this->body = $content . $this->getBody();
		return $this;
	}

	public function appendBody(string $content)
	{
		$this->body = $this->getBody() . $content;
		return $this;
	}

	/**
	 * @param string|null $name cookie name or null to get all
	 *
	 * @return array|null returns an associative array if a given cookie name is found or null
	 */
	public function getCookie(string $name) : ?array
	{
		return $this->cookies[$name] ?? null;
	}

	public function getCookies() : array
	{
		return $this->cookies;
	}

	/**
	 * @param string      $name
	 * @param string      $value
	 * @param int         $expires
	 * @param string      $domain
	 * @param string      $path
	 * @param bool        $secure
	 * @param bool        $httponly
	 * @param string|null $samesite
	 *
	 * @return $this
	 */
	public function setCookie(
		string $name,
		string $value = '',
		int $expires = 0,
		string $domain = '',
		string $path = '/',
		bool $secure = false,
		bool $httponly = false,
		string $samesite = null
	) {
		$expires = $expires < 1
			? \time() - 86500
			: \time() + $expires;
		$this->cookies[$name] = [
			'name' => $name,
			'value' => $value,
			'expires' => $expires,
			'path' => $path,
			'domain' => $domain,
			'secure' => $secure,
			'httponly' => $httponly,
			'samesite' => $samesite ? \ucfirst(\strtolower($samesite)) : null,
		];
		return $this;
	}

	/**
	 * @param string $name cookie name
	 *
	 * @return $this
	 */
	public function removeCookie(string $name)
	{
		unset($this->cookies[$name]);
		return $this;
	}

	public function removeCookies(array $names)
	{
		foreach ($names as $name) {
			$this->removeCookie($name);
		}
		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return string|null
	 */
	public function getHeader(string $name) : ?string
	{
		return $this->headers[$this->getHeaderName($name)] ?? null;
	}

	public function getHeaders() : array
	{
		return $this->headers;
	}

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setHeader(string $name, string $value)
	{
		$this->headers[$this->getHeaderName($name)] = $value;
		return $this;
	}

	public function setHeaders(array $headers)
	{
		foreach ($headers as $name => $value) {
			$this->setHeader($name, $value);
		}
		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function removeHeader(string $name)
	{
		unset($this->headers[$this->getHeaderName($name)]);
		return $this;
	}

	public function removeHeaders(array $names)
	{
		foreach ($names as $name) {
			$this->removeHeader($name);
		}
		return $this;
	}

	public function getProtocol() : string
	{
		return $this->getServer('SERVER_PROTOCOL') ?? 'HTTP/1.1';
	}

	protected function getServer(string $name)
	{
		if ($this->input['SERVER'] === null) {
			$this->input['SERVER'] = (array) \filter_input_array(\INPUT_SERVER);
			\ksort($this->input['SERVER']);
		}
		return $this->input['SERVER'][$name] ?? null;
	}

	/**
	 * @return string
	 */
	public function getStatusLine() : string
	{
		return "{$this->statusCode} {$this->statusReason}";
	}

	/**
	 * @param int         $code
	 * @param string|null $reason
	 *
	 * @throws \InvalidArgumentException if status code is invalid
	 * @throws \LogicException           is status code is unknown and a reason is not set
	 *
	 * @return $this
	 */
	public function setStatusLine(int $code, string $reason = null)
	{
		$this->setStatusCode($code);
		if (empty($reason) && empty($this->responseCodes[$code])) {
			throw new \LogicException("Unknown status code must have a reason: {$code}");
		}
		$this->setStatusReason($reason ?? $this->responseCodes[$code]);
		return $this;
	}

	/**
	 * @param int $code
	 *
	 * @throws \InvalidArgumentException if status code is invalid
	 *
	 * @return $this
	 */
	public function setStatusCode(int $code)
	{
		if ($code < 100 || $code > 599) {
			throw new \InvalidArgumentException("Invalid status code: {$code}");
		}
		$this->statusCode = $code;
		return $this;
	}

	public function getStatusCode() : int
	{
		return $this->statusCode;
	}

	public function setStatusReason(string $reason)
	{
		$this->statusReason = $reason;
		return $this;
	}

	public function getStatusReason() : string
	{
		return $this->statusReason;
	}

	/**
	 * @return bool
	 */
	public function isSent() : bool
	{
		return $this->isSent;
	}

	/**
	 * Sets the HTTP Redirect Response with data accessible in the next HTTP Request.
	 *
	 * @param string   $location the Location Header value
	 * @param int|null $code     HTTP Redirect status code. Leave null to determine based on the
	 *                           current HTTP method and protocol.
	 *
	 * @see  http://en.wikipedia.org/wiki/Post/Redirect/Get
	 *
	 * @throws \InvalidArgumentException for $with if the type is not array or false
	 *
	 * @return $this
	 *
	 * @todo See:
	 *       https://github.com/phalcon/cphalcon/blob/caeac66e7db02fd54c44a27cf22a6381654af2ee/phalcon/http/response.zep#L471
	 */
	/*public function redirect(string $location, array $with = [], int $code = null)
	{
		if (empty($code) &&
			(float) \str_ireplace(
				'HTTP/',
				'',
				(string) $this->getServer('SERVER_PROTOCOL')
			) >= 1.1) {
			// See: http://en.wikipedia.org/wiki/Post/Redirect/Get
			$code = $this->getServer('REQUEST_METHOD') === 'GET' ? 307 : 303;
		} elseif ($code < 300 || $code > 308) {
			$code = 302;
		}
		$this->setStatus($code);
		$this->setHeader('Location', $location);
		if (\array_key_exists('input', $with)) {
			if ($with['input'] !== false && ! \is_array($with['input'])) {
				throw new \InvalidArgumentException('Redirect with input must be an array or false.');
			}
			if ($with['input'] !== false) {
				switch ($this->request->getMethod()) {
					case 'POST':
						$input = $this->request->getPOST();
						break;
					case 'PATCH':
					case 'PUT':
						$input = $this->request->getBody(true);
						break;
					default:
						$input = [];
						break;
				}
				if (isset($with['input'])) {
					$with['input'] = \array_replace_recursive($input, $with['input']);
				} else {
					$with['input'] = $input;
				}
			} else {
				unset($with['input']);
			}
		} else {
			switch ($this->request->getMethod()) {
				case 'POST':
					$with['input'] = $this->request->getPOST();
					break;
				case 'PATCH':
				case 'PUT':
					$with['input'] = $this->request->getBody(true);
					break;
			}
		}
		if (\array_key_exists('errors', $with)) {
			if ($with['errors'] !== false && ! \is_array($with['errors'])) {
				throw new \InvalidArgumentException('Redirect with errors must be an array or false.');
			}
			if ($with['errors'] !== false) {
				$errors = Services::validation()->getErrors();
				if (isset($with['errors'])) {
					$with['errors'] = \array_replace_recursive($errors, $with['errors']);
				} else {
					$with['errors'] = $errors;
				}
			} else {
				unset($with['errors']);
			}
		} else {
			$with['errors'] = Services::validation()->getErrors();
		}
		if ($with) {
			session()->setFlash('$__REDIRECT', $with);
		}
		return $this;
	} */

	/**
	 * @throws \LogicException if Response already is sent
	 *
	 * @return $this
	 */
	public function send()
	{
		if ($this->isSent) {
			throw new \LogicException('Response already is sent');
		}
		$this->sendHeaders();
		$this->sendCookies();
		//$this->setBody($this->getBody() . (\ob_get_length() ? \ob_get_clean() : ''));
		$this->sendBody();
		$this->isSent = true;
		return $this;
	}

	/**
	 * @return $this
	 */
	protected function sendBody()
	{
		echo $this->getBody();
		return $this;
	}

	protected function sendCookies() : void
	{
		foreach ($this->cookies as $params) {
			$name = $params['name'];
			$value = $params['value'];
			unset($params['name'], $params['value']);
			\setcookie($name, $value, $params);
		}
	}

	protected function sendHeaders()
	{
		if (\headers_sent()) {
			// \var_dump(\headers_list());exit;
			throw new \LogicException('Headers already is sent');
		}
		// Per spec, MUST be sent with each request, if possible.
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html
		if ($this->getHeader('Date') === null) {
			$this->setDate(\DateTime::createFromFormat('U', \time()));
		}
		if ($this->getHeader('Content-Type') === null) {
			$this->setContentType('text/html');
		}
		// Is good to do this?
		/*if ($this->getCacheSeconds() && empty($this->headers['ETag']))
		{
			$this->setETag(\md5($this->getBody()));
		}*/
		\header($this->getProtocol() . ' ' . $this->getStatusLine());
		foreach ($this->headers as $name => $value) {
			\header($name . ($value ? ': ' . $value : ''));
		}
		return true;
	}

	/**
	 * @param mixed $data
	 * @param int   $options [optional] <p>
	 *                       Bitmask consisting of <b>JSON_HEX_QUOT</b>,
	 *                       <b>JSON_HEX_TAG</b>,
	 *                       <b>JSON_HEX_AMP</b>,
	 *                       <b>JSON_HEX_APOS</b>,
	 *                       <b>JSON_NUMERIC_CHECK</b>,
	 *                       <b>JSON_PRETTY_PRINT</b>,
	 *                       <b>JSON_UNESCAPED_SLASHES</b>,
	 *                       <b>JSON_FORCE_OBJECT</b>,
	 *                       <b>JSON_UNESCAPED_UNICODE</b>.
	 *                       <b>JSON_THROW_ON_ERROR</b>
	 *                       </p>
	 *                       <p>Default is <b>JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE</b>
	 *                       when null</p>
	 * @param int   $depth   [optional] <p>
	 *                       Set the maximum depth. Must be greater than zero.
	 *                       </p>
	 *
	 * @throws \JsonException if json_encode() fails
	 *
	 * @return $this
	 */
	public function setJSON($data, int $options = null, int $depth = 512)
	{
		if ($options === null) {
			$options = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;
		}
		$data = \json_encode($data, $options | \JSON_THROW_ON_ERROR, $depth);
		$this->setContentType('application/json');
		$this->setBody($data);
		return $this;
	}

	/**
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
	 * @see https://stackoverflow.com/a/3492459/6027968
	 *
	 * @param int  $seconds
	 * @param bool $public
	 *
	 * @return $this
	 */
	public function setCache(int $seconds, bool $public = false)
	{
		$date = new \DateTime();
		$date->modify('+' . $seconds . ' seconds');
		$this->setExpires($date);
		$this->setHeader(
			'Cache-Control',
			($public ? 'public' : 'private') . ', max-age=' . $seconds
		);
		$this->cacheSeconds = $seconds;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setNoCache()
	{
		$this->setHeader('Cache-Control', 'no-cache, no-store, max-age=0');
		$this->cacheSeconds = 0;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCacheSeconds() : int
	{
		return $this->cacheSeconds;
	}

	/**
	 * @param string $mime
	 * @param string $charset
	 *
	 * @return $this
	 */
	public function setContentType(string $mime, string $charset = 'UTF-8')
	{
		$this->setHeader('Content-Type', $mime . ($charset ? '; charset=' . $charset : ''));
		return $this;
	}

	public function setContentLanguage(string $language)
	{
		$this->setHeader('Content-Language', $language);
		return $this;
	}

	public function setContentEncoding(string $encoding)
	{
		$this->setHeader('Content-Encoding', $encoding);
		return $this;
	}

	public function setCSRFToken(string $token, int $ttl = 7200)
	{
		$this->setCookie(
			'X-CSRF-Token',
			$token,
			$ttl,
			'',
			'/',
			$this->request->isSecure(),
			true,
			'Strict'
		);
		return $this;
	}

	/**
	 * @param \DateTime $datetime
	 *
	 * @return $this
	 */
	public function setDate(\DateTime $datetime)
	{
		$date = clone $datetime;
		$date->setTimezone(new \DateTimeZone('UTC'));
		$this->setHeader('Date', $date->format('D, d M Y H:i:s') . ' GMT');
		return $this;
	}

	/**
	 * @param string $etag
	 *
	 * @return $this
	 */
	public function setETag(string $etag)
	{
		$this->setHeader('ETag', $etag);
		return $this;
	}

	/**
	 * @param \DateTime $datetime
	 *
	 * @return $this
	 */
	public function setExpires(\DateTime $datetime)
	{
		$date = clone $datetime;
		$date->setTimezone(new \DateTimeZone('UTC'));
		$this->setHeader('Expires', $date->format('D, d M Y H:i:s') . ' GMT');
		return $this;
	}

	/**
	 * @param \DateTime $datetime
	 *
	 * @return $this
	 */
	public function setLastModified(\DateTime $datetime)
	{
		$date = clone $datetime;
		$date->setTimezone(new \DateTimeZone('UTC'));
		$this->setHeader('Last-Modified', $date->format('D, d M Y H:i:s') . ' GMT');
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setNotModified()
	{
		$this->setStatusLine(304, 'Not Modified');
		return $this;
	}
}
