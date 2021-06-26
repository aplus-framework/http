<?php declare(strict_types=1);
/*
 * This file is part of The Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class URL.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Identifying_resources_on_the_Web
 * @see https://developer.mozilla.org/en-US/docs/Web/API/URL
 * @see https://tools.ietf.org/html/rfc3986#section-3
 */
class URL implements \JsonSerializable, \Stringable
{
	/**
	 * The #fragment (id).
	 */
	protected ?string $fragment = null;
	protected ?string $hostname = null;
	protected ?string $pass = null;
	/**
	 * The /paths/of/url.
	 *
	 * @var array<int,string>
	 */
	protected array $pathSegments = [];
	protected ?int $port = null;
	/**
	 *  The ?queries.
	 *
	 * @var array<string,mixed>
	 */
	protected array $queryData = [];
	protected ?string $scheme = null;
	protected ?string $user = null;

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
		return $this->getAsString();
	}

	/**
	 * @param string $query
	 * @param int|string|null $value
	 *
	 * @return static
	 */
	public function addQuery(string $query, $value = null) : static
	{
		$this->queryData[$query] = $value;
		return $this;
	}

	/**
	 * @param array<string,int|string|null> $queries
	 *
	 * @return static
	 */
	public function addQueries(array $queries) : static
	{
		foreach ($queries as $name => $value) {
			$this->addQuery($name, $value);
		}
		return $this;
	}

	/**
	 * @param array<int,string> $allowed
	 *
	 * @return array<string,mixed>
	 */
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

	/**
	 * @return array<string,mixed>
	 */
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

	/**
	 * @return array<int,string>
	 */
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
		$part = $this->getPort();
		if ( ! \in_array($part, [
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
	 * @param array<int,string> $allowedKeys Allowed query keys
	 *
	 * @return string|null
	 */
	public function getQuery(array $allowedKeys = []) : ?string
	{
		$query = $this->getQueryData($allowedKeys);
		return $query ? \http_build_query($query) : null;
	}

	/**
	 * @param array<int,string> $allowedKeys
	 *
	 * @return array<string,mixed>
	 */
	public function getQueryData(array $allowedKeys = []) : array
	{
		return $allowedKeys ? $this->filterQuery($allowedKeys) : $this->queryData;
	}

	/**
	 * @return string|null
	 */
	public function getScheme() : ?string
	{
		return $this->scheme;
	}

	public function getAsString() : string
	{
		$url = $this->getScheme() . '://';
		$part = $this->getUser();
		if ($part !== null) {
			$url .= $part;
			$part = $this->getPass();
			if ($part !== null) {
				$url .= ':' . $part;
			}
			$url .= '@';
		}
		$url .= $this->getHost();
		$url .= $this->getPath();
		$part = $this->getQuery();
		if ($part !== null) {
			$url .= '?' . $part;
		}
		$part = $this->getFragment();
		if ($part !== null) {
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
	 * @return static
	 */
	public function removeQueryData(string $key) : static
	{
		unset($this->queryData[$key]);
		return $this;
	}

	/**
	 * @param string $fragment
	 *
	 * @return static
	 */
	public function setFragment(string $fragment) : static
	{
		$this->fragment = \ltrim($fragment, '#');
		return $this;
	}

	/**
	 * @param string $hostname
	 *
	 * @throws InvalidArgumentException for invalid URL Hostname
	 *
	 * @return static
	 */
	public function setHostname(string $hostname) : static
	{
		$filtered = \filter_var($hostname, \FILTER_VALIDATE_DOMAIN, \FILTER_FLAG_HOSTNAME);
		if ( ! $filtered) {
			throw new InvalidArgumentException("Invalid URL Hostname: {$hostname}");
		}
		$this->hostname = $filtered;
		return $this;
	}

	/**
	 * @param string $pass
	 *
	 * @return static
	 */
	public function setPass(string $pass) : static
	{
		$this->pass = $pass;
		return $this;
	}

	/**
	 * @param string $segments
	 *
	 * @return static
	 */
	public function setPath(string $segments) : static
	{
		return $this->setPathSegments(\explode('/', \trim($segments, '/')));
	}

	/**
	 * @param array<int,string> $segments
	 *
	 * @return static
	 */
	public function setPathSegments(array $segments) : static
	{
		$this->pathSegments = $segments;
		return $this;
	}

	/**
	 * @param int $port
	 *
	 * @throws InvalidArgumentException for invalid URL Port
	 *
	 * @return static
	 */
	public function setPort(int $port) : static
	{
		if ($port < 1 || $port > 65535) {
			throw new InvalidArgumentException("Invalid URL Port: {$port}");
		}
		$this->port = $port;
		return $this;
	}

	/**
	 * @param string $data
	 * @param array<int,string> $only
	 *
	 * @return static
	 */
	public function setQuery(string $data, array $only = []) : static
	{
		\parse_str(\ltrim($data, '?'), $data);
		return $this->setQueryData($data, $only);
	}

	/**
	 * @param array<string,mixed> $data
	 * @param array<int,string> $only
	 *
	 * @return static
	 */
	public function setQueryData(array $data, array $only = []) : static
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
	 * @return static
	 */
	public function setScheme(string $scheme) : static
	{
		$this->scheme = $scheme;
		return $this;
	}

	/**
	 * @param string $url
	 *
	 * @throws InvalidArgumentException for invalid URL
	 *
	 * @return static
	 */
	protected function setURL(string $url) : static
	{
		$filtered_url = \filter_var($url, \FILTER_VALIDATE_URL);
		if ( ! $filtered_url) {
			throw new InvalidArgumentException("Invalid URL: {$url}");
		}
		$url = \parse_url($filtered_url);
		if ($url === false) {
			throw new RuntimeException("URL could not be parsed: {$filtered_url}");
		}
		$this->setScheme($url['scheme']); // @phpstan-ignore-line
		if (isset($url['user'])) {
			$this->setUser($url['user']);
		}
		if (isset($url['pass'])) {
			$this->setPass($url['pass']);
		}
		$this->setHostname($url['host']); // @phpstan-ignore-line
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
	 * @return static
	 */
	public function setUser(string $user) : static
	{
		$this->user = $user;
		return $this;
	}

	public function jsonSerialize()
	{
		return $this->getAsString();
	}
}
