<?php namespace Tests\HTTP;

use Framework\HTTP\UserAgent;

class RequestMock extends \Framework\HTTP\Request
{
	/**
	 * @var array<string,mixed>|null
	 */
	public ?array $parsedBody = null;
	public UserAgent | false $userAgent;
	/**
	 * @var array<int,array>
	 */
	public static array $input = [
		\INPUT_SERVER => [
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
			'HTTP_ACCEPT_LANGUAGE' => 'pt-BR,es;q=0.8,en;q=0.5,en-US;q=0.3',
			'HTTP_ACCEPT_CHARSET' => 'utf-8, iso-8859-1;q=0.5, *;q=0.1',
			'HTTP_CONTENT_TYPE' => 'text/html; charset=UTF-8',
			'HTTP_ETAG' => 'abc',
			'HTTP_HOST' => 'domain.tld',
			'HTTP_REFERER' => 'http://domain.tld/contact.html',
			'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
			'HTTP_X_REQUESTED_WITH' => 'XMLHTTPREQUEST',
			'REMOTE_ADDR' => '192.168.1.100',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_SCHEME' => 'http',
			'REQUEST_URI' => '/blog/posts?order_by=title&order=asc',
			'SERVER_PORT' => 80,
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'SERVER_NAME' => 'domain.tld',
		],
		\INPUT_POST => [
			'username' => 'phpdev',
			'password' => 'Aw3S0me',
			'user' => [
				'name' => 'foo',
				'city' => 'bar',
			],
			'csrf_token' => 'foo',
		],
		\INPUT_GET => [
			'order_by' => 'title',
			'order' => 'asc',
		],
		\INPUT_COOKIE => [
			'session_id' => 'abc',
			'cart' => 'cart-123',
			'status-bar' => 'open',
			'X-CSRF-Token' => 'token',
		],
		\INPUT_ENV => [],
	];

	public function __construct(array $allowedHosts = null)
	{
		$this->prepareInput();
		parent::__construct($allowedHosts);
	}

	protected function prepareInput() : void
	{
		$_SERVER = static::$input[\INPUT_SERVER];
		$_POST = static::$input[\INPUT_POST];
		$_GET = static::$input[\INPUT_GET];
		$_COOKIE = static::$input[\INPUT_COOKIE];
		$_ENV = static::$input[\INPUT_ENV];
	}

	public function filterInput(
		int $type,
		string $variable = null,
		int $filter = null,
		array | int $options = null
	) : mixed {
		return parent::filterInput($type, $variable, $filter, $options);
	}

	public function setHeader(string $name, string $value)
	{
		return parent::setHeader($name, $value);
	}

	public function removeHeader(string $name)
	{
		return parent::removeHeader($name);
	}

	public function setMethod(string $method)
	{
		return parent::setMethod($method);
	}

	public function setHost(string $host)
	{
		return parent::setHost($host);
	}

	/**
	 * @param int $type One of the INPUT_* constants
	 * @param array<string,mixed> $variables
	 */
	public static function setInput(int $type, array $variables) : void
	{
		foreach ($variables as $key => $value) {
			static::$input[$type][$key] = $value;
		}
	}
}
