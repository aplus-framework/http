<?php namespace Tests\HTTP;

class RequestProxyMock extends RequestMock
{
	public string $body = '{"test":123}';

	public function __construct(array $allowed_hosts)
	{
		$this->input[\INPUT_SERVER] = [
			'HTTP_HOST' => 'real-domain.tld:8080',
			'HTTP_X_FORWARDED_FOR' => '192.168.1.2',
			'HTTP_REFERER' => 'invali_d',
			'REMOTE_ADDR' => '192.168.1.100',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_SCHEME' => 'http',
			'HTTPS' => 'on',
			'REQUEST_URI' => '/blog/posts?order_by=title&order=asc',
			'SERVER_PORT' => 8080,
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'SERVER_NAME' => 'domain.tld',
		];
		parent::__construct($allowed_hosts);
	}

	protected function prepareCookies()
	{
		parent::prepareCookies();
		$this->removeCookie('X-CSRF-Token');
	}
}
