<?php namespace Framework\HTTP;

/**
 * Class URL.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Identifying_resources_on_the_Web
 * @see     https://developer.mozilla.org/en-US/docs/Web/API/URL
 * @see     https://tools.ietf.org/html/rfc3986#section-3
 */
class URL implements \JsonSerializable
{
	/**
	 * @var string|null The #fragment (id)
	 */
	protected $fragment;
	/**
	 * @var string|null
	 */
	protected $hostname;
	/**
	 * @var string|null
	 */
	protected $pass;
	/**
	 * @var array The /paths/of/url
	 */
	protected $pathSegments = [];
	/**
	 * @var int|null
	 */
	protected $port;
	/**
	 * @var array The ?queries
	 */
	protected $queryData = [];
	/**
	 * @var string|null
	 */
	protected $scheme;
	/**
	 * @var string|null
	 */
	protected $user;

	/**
	 * URL constructor.
	 *
	 * @param string $url
	 */
	public function __construct(string $url)
	{
		$this->setURL($url);
	}

	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->getURL();
	}

	/**
	 * @param string          $query
	 * @param int|string|null $value
	 *
	 * @return $this
	 */
	public function addQuery(string $query, $value = null)
	{
		$this->queryData[$query] = $value;
		return $this;
	}

	public function addQueries(array $queries)
	{
		foreach ($queries as $name => $value) {
			$this->addQuery($name, $value);
		}
		return $this;
	}

	protected function filterQuery(array $allowed) : array
	{
		return $this->queryData ?
			\array_intersect_key($this->queryData, \array_flip($allowed))
			: [];
	}

	public function getBaseURL(string $path = '/') : string
	{
		if ($path && $path !== '/') {
			$path = '/' . \trim($path, '/');
		}
		return $this->getOrigin() . $path;
	}

	/**
	 * @return string|null
	 */
	public function getFragment() : ?string
	{
		return $this->fragment;
	}

	/**
	 * @return string|null
	 */
	public function getHost() : ?string
	{
		return $this->hostname === null ? null : $this->hostname . $this->getPortPart();
	}

	public function getHostname() : ?string
	{
		return $this->hostname;
	}

	public function getOrigin() : string
	{
		return $this->getScheme() . '://' . $this->getHost();
	}

	public function getParsedURL() : array
	{
		return [
			'scheme' => $this->getScheme(),
			'user' => $this->getUser(),
			'pass' => $this->getPass(),
			'hostname' => $this->getHostname(),
			'port' => $this->getPort(),
			'path' => $this->getPathSegments(),
			'query' => $this->getQueryData(),
			'fragment' => $this->getFragment(),
		];
	}

	/**
	 * @return string|null
	 */
	public function getPass() : ?string
	{
		return $this->pass;
	}

	public function getPath() : string
	{
		return '/' . \implode('/', $this->pathSegments);
	}

	public function getPathSegments() : array
	{
		return $this->pathSegments;
	}

	public function getPathSegment(int $index) : ?string
	{
		return $this->pathSegments[$index] ?? null;
	}

	/**
	 * @return int|null
	 */
	public function getPort() : ?int
	{
		return $this->port;
	}

	protected function getPortPart() : string
	{
		if ( ! \in_array($part = $this->getPort(), [
			null,
			80,
			443,
		], true)) {
			return ':' . $part;
		}
		return '';
	}

	/**
	 * Get the "Query" part of the URL.
	 *
	 * @param array|null $allowed_keys Allowed query keys
	 *
	 * @return string|null
	 */
	public function getQuery(array $allowed_keys = []) : ?string
	{
		$query = $this->getQueryData($allowed_keys);
		return $query ? \http_build_query($query) : null;
	}

	public function getQueryData(array $allowed_keys = []) : array
	{
		return $allowed_keys ? $this->filterQuery($allowed_keys) : $this->queryData;
	}

	/**
	 * @return string|null
	 */
	public function getScheme() : ?string
	{
		return $this->scheme;
	}

	public function getURL() : string
	{
		$url = $this->getScheme() . '://';
		if ($part = $this->getUser()) {
			$url .= $part;
			if ($part = $this->getPass()) {
				$url .= ':' . $part;
			}
			$url .= '@';
		}
		$url .= $this->getHost();
		$url .= $this->getPath();
		if ($part = $this->getQuery()) {
			$url .= '?' . $part;
		}
		if ($part = $this->getFragment()) {
			$url .= '#' . $part;
		}
		return $url;
	}

	/**
	 * @return string|null
	 */
	public function getUser() : ?string
	{
		return $this->user;
	}

	/**
	 * @param string $key
	 *
	 * @return $this
	 */
	public function removeQueryData(string $key)
	{
		unset($this->queryData[$key]);
		return $this;
	}

	/**
	 * @param string $fragment
	 *
	 * @return $this
	 */
	public function setFragment(string $fragment)
	{
		$this->fragment = \ltrim($fragment, '#');
		return $this;
	}

	/**
	 * @param string $hostname
	 *
	 * @throws \InvalidArgumentException for invalid URL Hostname
	 *
	 * @return $this
	 */
	public function setHostname(string $hostname)
	{
		if ( ! $filtered = \filter_var(
			$hostname,
			\FILTER_VALIDATE_DOMAIN,
			\FILTER_FLAG_HOSTNAME
		)) {
			throw new \InvalidArgumentException("Invalid URL Hostname: {$hostname}");
		}
		$this->hostname = $filtered;
		return $this;
	}

	/**
	 * @param string $pass
	 *
	 * @return $this
	 */
	public function setPass(string $pass)
	{
		$this->pass = $pass;
		return $this;
	}

	/**
	 * @param string $segments
	 *
	 * @return $this
	 */
	public function setPath(string $segments)
	{
		return $this->setPathSegments(\explode('/', \trim($segments, '/')));
	}

	public function setPathSegments(array $segments)
	{
		$this->pathSegments = $segments;
		return $this;
	}

	/**
	 * @param int $port
	 *
	 * @throws \InvalidArgumentException for invalid URL Port
	 *
	 * @return $this
	 */
	public function setPort(int $port)
	{
		if ($port < 1 || $port > 65535) {
			throw new \InvalidArgumentException("Invalid URL Port: {$port}");
		}
		$this->port = $port;
		return $this;
	}

	/**
	 * @param string $data
	 * @param array  $only
	 *
	 * @return $this
	 */
	public function setQuery(string $data, array $only = [])
	{
		\parse_str(\ltrim($data, '?'), $data);
		return $this->setQueryData($data, $only);
	}

	public function setQueryData(array $data, array $only = [])
	{
		if ($only) {
			$data = \array_intersect_key($data, \array_flip($only));
		}
		$this->queryData = $data;
		return $this;
	}

	/**
	 * @param string $scheme
	 *
	 * @return $this
	 */
	public function setScheme(string $scheme)
	{
		$this->scheme = $scheme;
		return $this;
	}

	/**
	 * @param string $url
	 *
	 * @throws \InvalidArgumentException for invalid URL
	 *
	 * @return $this
	 */
	protected function setURL(string $url)
	{
		if ( ! $filtered_url = \filter_var($url, \FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException("Invalid URL: {$url}");
		}
		$url = \parse_url($filtered_url);
		$this->setScheme($url['scheme']);
		if (isset($url['user'])) {
			$this->setUser($url['user']);
		}
		if (isset($url['pass'])) {
			$this->setPass($url['pass']);
		}
		$this->setHostname($url['host']);
		if (isset($url['port'])) {
			$this->setPort($url['port']);
		}
		if (isset($url['path'])) {
			$this->setPath($url['path']);
		}
		if (isset($url['query'])) {
			$this->setQuery($url['query']);
		}
		if (isset($url['fragment'])) {
			$this->setFragment($url['fragment']);
		}
		return $this;
	}

	/**
	 * @param string $user
	 *
	 * @return $this
	 */
	public function setUser(string $user)
	{
		$this->user = $user;
		return $this;
	}

	public function jsonSerialize()
	{
		return $this->getURL();
	}
}
