<?php namespace Framework\HTTP;

use Framework\HTTP\Exceptions\URLException;

/**
 * Class URL
 *
 * @see     https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Identifying_resources_on_the_Web
 *
 * @package Framework\HTTP
 */
class URL
{
	/**
	 * @var null|string
	 */
	protected $scheme;
	/**
	 * @var null|string
	 */
	protected $user;
	/**
	 * @var null|string
	 */
	protected $pass;
	/**
	 * @var null|string
	 */
	protected $host;
	/**
	 * @var null|int
	 */
	protected $port;
	/**
	 * @var array The /paths/of/url
	 */
	protected $path = [];
	/**
	 * @var array The ?queries
	 */
	protected $query = [];
	/**
	 * @var null|string The #fragment (id)
	 */
	protected $fragment;

	/**
	 * URL constructor.
	 *
	 * @param string|null $url
	 */
	public function __construct(string $url = null)
	{
		if ($url !== null)
		{
			$this->setURL($url);
		}
	}

	/**
	 * @param string $url
	 *
	 * @return $this
	 */
	public function setURL(string $url)
	{
		if ( ! $filtered_url = \filter_var($url, \FILTER_VALIDATE_URL))
		{
			throw URLException::forInvalidURL($url);
		}

		$url = \parse_url($filtered_url);

		$this->setScheme($url['scheme']);

		if (isset($url['user']))
		{
			$this->setUser($url['user']);
		}

		if (isset($url['pass']))
		{
			$this->setPass($url['pass']);
		}

		$this->setHost($url['host']);

		if (isset($url['port']))
		{
			$this->setPort($url['port']);
		}

		if (isset($url['path']))
		{
			$this->setPath($url['path']);
		}

		if (isset($url['query']))
		{
			\parse_str($url['query'], $url['query']);

			$this->setQuery($url['query']);
		}

		if (isset($url['fragment']))
		{
			$this->setFragment($url['fragment']);
		}

		return $this;
	}

	public function getBaseURL(string $path = '/'): string
	{
		$url = $this->getScheme() . '://' . $this->getHost();

		if ( ! \in_array($part = $this->getPort(), [
			null,
			80,
			443,
		]))
		{

			$url .= ':' . $part;
		}

		return $url . $path;
	}

	/**
	 * @param bool $as_array
	 *
	 * @return array|string
	 */
	public function getURL(bool $as_array = false)
	{
		if ($as_array)
		{
			return [
				'scheme'   => $this->getScheme(),
				'user'     => $this->getUser(),
				'pass'     => $this->getPass(),
				'host'     => $this->getHost(),
				'port'     => $this->getPort(),
				'path'     => $this->getPath(true),
				'query'    => $this->getQuery(true),
				'fragment' => $this->getFragment(),
			];
		}

		$url = $this->getScheme() . '://';

		if ($part = $this->getUser())
		{
			$url .= $part;

			if ($part = $this->getPass())
			{
				$url .= ':' . $part;
			}

			$url .= '@';
		}

		$url .= $this->getHost();

		if ( ! \in_array($part = $this->getPort(), [
			null,
			80,
			443,
		]))
		{

			$url .= ':' . $part;
		}

		$url .= $this->getPath();

		if ($part = $this->getQuery())
		{
			$url .= '?' . $part;
		}

		if ($part = $this->getFragment())
		{
			$url .= '#' . $part;
		}

		return $url;
	}

	/**
	 * @return null|string
	 */
	public function getScheme(): ?string
	{
		return $this->scheme;
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
	 * @return null|string
	 */
	public function getHost(): ?string
	{
		return $this->host;
	}

	/**
	 * @param string $host
	 *
	 * @return $this
	 */
	public function setHost(string $host)
	{
		if ( ! $filtered_host = \filter_var($host, \FILTER_VALIDATE_DOMAIN, \FILTER_FLAG_HOSTNAME))
		{
			throw URLException::forInvalidHost($host);
		}

		$this->host = $filtered_host;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getPort(): ?int
	{
		return $this->port;
	}

	/**
	 * @param int $port
	 *
	 * @return $this
	 */
	public function setPort(int $port)
	{
		if ($port < 1 || $port > 65535)
		{
			throw URLException::forInvalidPort($port);
		}

		$this->port = $port;

		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getUser(): ?string
	{
		return $this->user;
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

	/**
	 * @return null|string
	 */
	public function getPass(): ?string
	{
		return $this->pass;
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
	 * @param bool $exploded
	 *
	 * @return array|string
	 */
	public function getPath(bool $exploded = false)
	{
		if ($exploded)
		{
			return $this->path;
		}

		return '/' . \implode('/', $this->path);
	}

	/**
	 * @param $segments
	 *
	 * @return $this
	 */
	public function setPath($segments)
	{
		if (\is_array($segments))
		{
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
	 * Get the "Query" part of the URL
	 *
	 * @param bool       $exploded If true will return an array, otherwise a string
	 * @param array|null $queries  Allowed queries
	 *
	 * @return string|array
	 */
	public function getQuery(bool $exploded = false, array $queries = null)
	{
		if ($queries)
		{
			$queries = \array_intersect_key($this->query, \array_flip($queries));
		}

		if ($exploded)
		{
			return $queries ?? $this->query;
		}

		return \urldecode(\http_build_query($queries ?? $this->query));
	}

	/**
	 * @param string|array $query
	 * @param array|null   $only
	 *
	 * @return $this
	 */
	public function setQuery($query, array $only = null)
	{
		if ( ! \is_array($query))
		{
			\parse_str(\trim($query, '?'), $query);
		}

		if ($only)
		{
			$query = \array_intersect_key($query, \array_flip($only));
		}

		$this->query = $query;

		return $this;
	}

	/**
	 * @param string|array    $query
	 * @param null|string|int $value
	 *
	 * @return $this
	 */
	public function addQuery($query, $value = null)
	{
		if (\is_array($query))
		{
			foreach ($query as $name => $value)
			{
				$this->query[$name] = $value;
			}

			return $this;
		}

		$this->query[$query] = $value;

		return $this;
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
	 * @return null|string
	 */
	public function getFragment(): ?string
	{
		return $this->fragment;
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
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getURL();
	}
}
