<?php namespace Tests\HTTP;

use Framework\HTTP\Cookie;

class RequestMock extends \Framework\HTTP\Request
{
	public $parsedBody;
	public $body;
	public $userAgent;

	protected function prepareServerVariables()
	{
		$this->serverVariables = [
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
	}

	public function setServerVariable(string $name, $value)
	{
		$this->serverVariables[$name] = $value;
	}

	protected function prepareBody()
	{
		$this->setBody('color=red&height=500px&width=800');
	}

	protected function preparePOST()
	{
		$this->post = [
			'username' => 'phpdev',
			'password' => 'Aw3S0me',
			'user' => [
				'name' => 'foo',
				'city' => 'bar',
			],
		];
	}

	protected function prepareGET()
	{
		$this->get = [
			'order_by' => 'title',
			'order' => 'asc',
		];
	}

	protected function prepareCookies()
	{
		$this->setCookie(new Cookie('session_id', 'abc'));
		$this->setCookie(new Cookie('cart', 'cart-123'));
		$this->setCookie(new Cookie('status-bar', 'open'));
		$this->setCookie(new Cookie('X-CSRF-Token', 'token'));
	}

	public function setHeader(string $name, string $value)
	{
		return parent::setHeader($name, $value);
	}
}
