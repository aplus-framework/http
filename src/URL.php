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

use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

/**
 * Class URL.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Identifying_resources_on_the_Web#urls
 * @see https://developer.mozilla.org/en-US/docs/Web/API/URL
 * @see https://datatracker.ietf.org/doc/html/rfc3986#section-3
 *
 * @package http
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
        $this->setUrl($url);
    }

    /**
     * @return string
     */
    #[Pure]
    public function __toString() : string
    {
        return $this->toString();
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
    #[Pure]
    protected function filterQuery(array $allowed) : array
    {
        return $this->queryData ?
            \array_intersect_key($this->queryData, \array_flip($allowed))
            : [];
    }

    #[Pure]
    public function getBaseUrl(string $path = '/') : string
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
    #[Pure]
    public function getHost() : ?string
    {
        return $this->hostname === null ? null : $this->hostname . $this->getPortPart();
    }

    #[Pure]
    public function getHostname() : ?string
    {
        return $this->hostname;
    }

    #[Pure]
    public function getOrigin() : string
    {
        return $this->getScheme() . '://' . $this->getHost();
    }

    /**
     * @return array<string,mixed>
     */
    #[ArrayShape([
        'scheme' => 'string',
        'user' => 'null|string',
        'pass' => 'null|string',
        'hostname' => 'string',
        'port' => 'int|null',
        'path' => 'string[]',
        'query' => 'mixed[]',
        'fragment' => 'null|string',
    ])]
    #[Pure]
    public function getParsedUrl() : array
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
    #[Pure]
    public function getPass() : ?string
    {
        return $this->pass;
    }

    #[Pure]
    public function getPath() : string
    {
        return '/' . \implode('/', $this->pathSegments);
    }

    /**
     * @return array<int,string>
     */
    #[Pure]
    public function getPathSegments() : array
    {
        return $this->pathSegments;
    }

    #[Pure]
    public function getPathSegment(int $index) : ?string
    {
        return $this->pathSegments[$index] ?? null;
    }

    /**
     * @return int|null
     */
    #[Pure]
    public function getPort() : ?int
    {
        return $this->port;
    }

    #[Pure]
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
    #[Pure]
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
    #[Pure]
    public function getQueryData(array $allowedKeys = []) : array
    {
        return $allowedKeys ? $this->filterQuery($allowedKeys) : $this->queryData;
    }

    /**
     * @return string|null
     */
    #[Pure]
    public function getScheme() : ?string
    {
        return $this->scheme;
    }

    /**
     * @return string
     *
     * @deprecated Use {@see URL::toString()}
     *
     * @codeCoverageIgnore
     */
    #[Deprecated(
        reason: 'since HTTP Library version 5.3, use toString() instead',
        replacement: '%class%->toString()'
    )]
    public function getAsString() : string
    {
        \trigger_error(
            'Method ' . __METHOD__ . ' is deprecated',
            \E_USER_DEPRECATED
        );
        return $this->toString();
    }

    /**
     * @since 5.3
     *
     * @return string
     */
    #[Pure]
    public function toString() : string
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
    #[Pure]
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
     * @param array<string> $only
     *
     * @return static
     */
    public function setQuery(string $data, array $only = []) : static
    {
        \parse_str(\ltrim($data, '?'), $data);
        return $this->setQueryData($data, $only);
    }

    /**
     * @param array<mixed> $data
     * @param array<string> $only
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
    protected function setUrl(string $url) : static
    {
        $filteredUrl = \filter_var($url, \FILTER_VALIDATE_URL);
        if ( ! $filteredUrl) {
            throw new InvalidArgumentException("Invalid URL: {$url}");
        }
        $url = \parse_url($filteredUrl);
        if ($url === false) {
            throw new RuntimeException("URL could not be parsed: {$filteredUrl}");
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

    #[Pure]
    public function jsonSerialize() : string
    {
        return $this->toString();
    }
}
