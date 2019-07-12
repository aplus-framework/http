<?php namespace Tests\HTTP;

class RequestMock extends \Framework\HTTP\Request
{
	public $parsedBody;
	public $body;
	public $userAgent;

	protected function filterInput(int $type) : array
	{
		parent::filterInput($type);
		if ($type === \INPUT_SERVER) {
			return [
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
		if ($type === \INPUT_POST) {
			return [
				'username' => 'phpdev',
				'password' => 'Aw3S0me',
				'user' => [
					'name' => 'foo',
					'city' => 'bar',
				],
			];
		}
		if ($type === \INPUT_GET) {
			return [
				'order_by' => 'title',
				'order' => 'asc',
			];
		}
		if ($type === \INPUT_COOKIE) {
			return [
				'session_id' => 'abc',
				'cart' => 'cart-123',
				'status-bar' => 'open',
				'X-CSRF-Token' => 'token',
			];
		}
		if ($type === \INPUT_ENV) {
			return [];
		}
		return [];
	}

	public function setServerVariable(string $name, $value)
	{
		$this->serverVariables[$name] = $value;
	}

	protected function prepareBody()
	{
		parent::prepareBody();
		$this->setBody('color=red&height=500px&width=800');
	}

	public function setHeader(string $name, string ...$values)
	{
		return parent::setHeader($name, ...$values);
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
