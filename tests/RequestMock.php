<?php namespace Tests\HTTP;

use Framework\HTTP\UserAgent;

class RequestMock extends \Framework\HTTP\Request
{
	public ?array $parsedBody = null;
	public UserAgent | false | null $userAgent = null;

	public function __construct(array $allowed_hosts = null)
	{
		$this->prepareInput();
		parent::__construct($allowed_hosts);
	}

	protected function prepareInput()
	{
		$_SERVER = [
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
		];
		$_POST = [
			'username' => 'phpdev',
			'password' => 'Aw3S0me',
			'user' => [
				'name' => 'foo',
				'city' => 'bar',
			],
			'csrf_token' => 'foo',
		];
		$_GET = [
			'order_by' => 'title',
			'order' => 'asc',
		];
		$_COOKIE = [
			'session_id' => 'abc',
			'cart' => 'cart-123',
			'status-bar' => 'open',
			'X-CSRF-Token' => 'token',
		];
		$_ENV = [];
	}

	public function filterInput(
		int $type,
		string $variable = null,
		int $filter = null,
		array | int $options = null
	) : mixed {
		return parent::filterInput($type, $variable, $filter, $options);
	}

	public function setServerVariable(string $name, $value)
	{
		$this->input[\INPUT_SERVER][$name] = $value;
	}

	public function setHeader(string $name, string $value)
	{
		return parent::setHeader($name, $value);
	}

	public function setEmptyHeader(string $name)
	{
		$this->headers[\strtolower($name)] = null;
	}

	public function setMethod(string $method)
	{
		return parent::setMethod($method);
	}

	public function setHost(string $host)
	{
		return parent::setHost($host);
	}
}
