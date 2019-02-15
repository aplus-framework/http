<?php namespace Framework\HTTP;

use Framework\HTTP\Exceptions\URLException;

/**
 * Class URL.
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Identifying_resources_on_the_Web
 */
class URL
{
	/**
	 * @var string|null The #fragment (id)
	 */
	protected $fragment;
	/**
	 * @var string|null
	 */
	protected $host;
	/**
	 * @var string|null
	 */
	protected $pass;
	/**
	 * @var array The /paths/of/url
	 */
	protected $path = [];
	/**
	 * @var int|null
	 */
	protected $port;
	/**
	 * @var array The ?queries
	 */
	protected $query = [];
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
	 * @param string|null $url
	 */
	public function __construct(string $url = null)
	{
		if ($url !== null) {
			$this->setURL($url);
		}
	}

	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->getURL();
	}

	/**
	 * @param array|string    $query
	 * @param int|string|null $value
	 *
	 * @return $this
	 */
	public function addQuery($query, $value = null)
	{
		if (\is_array($query)) {
			foreach ($query as $name => $value) {
				$this->query[$name] = $value;
			}
			return $this;
		}
		$this->query[$query] = $value;
		return $this;
	}

	public function getBaseURL(string $path = '/') : string
	{
		$url = $this->getScheme() . '://' . $this->getHost();
		if ( ! \in_array($part = $this->getPort(), [
			null,
			80,
			443,
		])) {
			$url .= ':' . $part;
		}
		return $url . $path;
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
		return $this->host;
	}

	/**
	 * @return string|null
	 */
	public function getPass() : ?string
	{
		return $this->pass;
	}

	/**
	 * @param bool $exploded
	 *
	 * @return array|string
	 */
	public function getPath(bool $exploded = false)
	{
		if ($exploded) {
			return $this->path;
		}
		return '/' . \implode('/', $this->path);
	}

	/**
	 * @return int|null
	 */
	public function getPort() : ?int
	{
		return $this->port;
	}

	/**
	 * Get the "Query" part of the URL.
	 *
	 * @param bool       $exploded If true will return an array, otherwise a string
	 * @param array|null $queries  Allowed queries
	 *
	 * @return array|string
	 */
	public function getQuery(bool $exploded = false, array $queries = null)
	{
		if ($queries) {
			$queries = \array_intersect_key($this->query, \array_flip($queries));
		}
		if ($exploded) {
			return $queries ?? $this->query;
		}
		return \urldecode(\http_build_query($queries ?? $this->query));
	}

	/**
	 * @return string|null
	 */
	public function getScheme() : ?string
	{
		return $this->scheme;
	}

	/**
	 * @param bool $as_array
	 *
	 * @return array|string
	 */
	public function getURL(bool $as_array = false)
	{
		if ($as_array) {
			return [
				'scheme' => $this->getScheme(),
				'user' => $this->getUser(),
				'pass' => $this->getPass(),
				'host' => $this->getHost(),
				'port' => $this->getPort(),
				'path' => $this->getPath(true),
				'query' => $this->getQuery(true),
				'fragment' => $this->getFragment(),
			];
		}
		$url = $this->getScheme() . '://';
		if ($part = $this->getUser()) {
			$url .= $part;
			if ($part = $this->getPass()) {
				$url .= ':' . $part;
			}
			$url .= '@';
		}
		$url .= $this->getHost();
		if ( ! \in_array($part = $this->getPort(), [
			null,
			80,
			443,
		])) {
			$url .= ':' . $part;
		}
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
	 * @param string $query
	 *
	 * @return $this
	 */
	public function removeQuery(string $query)
	{
		unset($this->query[$query]);
		return $this;
	}

	/**
	 * @param string $fragment
	 *
	 * @return $this
	 */
	public function setFragment(string $fragment)
	{
		$this->fragment = $fragment;
		return $this;
	}

	/**
	 * @param string $host
	 *
	 * @return $this
	 */
	public function setHost(string $host)
	{
		if ( ! $filtered_host = \filter_var(
			$host,
			\FILTER_VALIDATE_DOMAIN,
			\FILTER_FLAG_HOSTNAME
		)) {
			throw URLException::forInvalidHost($host);
		}
		$this->host = $filtered_host;
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
	 * @param $segments
	 *
	 * @return $this
	 */
	public function setPath($segments)
	{
		if (\is_array($segments)) {
			$this->path = $segments;
			return $this;
		}
		$segments = \trim($segments, '/');
		$this->path = \explode('/', $segments);
		/*if (\end($this->path) === '')
		{
			\array_pop($this->path);
		}

		if (isset($this->path[0]) && $this->path[0] === '')
		{
			$this->path[0] = '/';
		}*/
		return $this;
	}

	/**
	 * @param int $port
	 *
	 * @return $this
	 */
	public function setPort(int $port)
	{
		if ($port < 1 || $port > 65535) {
			throw URLException::forInvalidPort($port);
		}
		$this->port = $port;
		return $this;
	}

	/**
	 * @param array|string $query
	 * @param array|null   $only
	 *
	 * @return $this
	 */
	public function setQuery($query, array $only = null)
	{
		if ( ! \is_array($query)) {
			\parse_str(\trim($query, '?'), $query);
		}
		if ($only) {
			$query = \array_intersect_key($query, \array_flip($only));
		}
		$this->query = $query;
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
	 * @return $this
	 */
	public function setURL(string $url)
	{
		if ( ! $filtered_url = \filter_var($url, \FILTER_VALIDATE_URL)) {
			throw URLException::forInvalidURL($url);
		}
		$url = \parse_url($filtered_url);
		$this->setScheme($url['scheme']);
		if (isset($url['user'])) {
			$this->setUser($url['user']);
		}
		if (isset($url['pass'])) {
			$this->setPass($url['pass']);
		}
		$this->setHost($url['host']);
		if (isset($url['port'])) {
			$this->setPort($url['port']);
		}
		if (isset($url['path'])) {
			$this->setPath($url['path']);
		}
		if (isset($url['query'])) {
			\parse_str($url['query'], $url['query']);
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
}
