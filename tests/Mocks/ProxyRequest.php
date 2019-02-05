<?php namespace Tests\HTTP\Mocks;

class ProxyRequest extends \Framework\HTTP\Request
{
	protected $body  = '{"test":123}';
	protected $input = [
		'POST'     => [
			'username' => 'phpdev',
			'password' => 'Aw3S0me',
		],
		'GET'      => [
			'order_by' => 'title',
			'order'    => 'asc',
		],
		'COOKIE'   => [
			'session_id' => 'abc',
			'cart'       => 'cart-123',
			'status-bar' => 'open',
		],
		'ENV'      => [],
		'SERVER'   => [
			'HTTP_HOST'            => 'real-domain.tld',
			'HTTP_X_FORWARDED_FOR' => '192.168.1.2',
			'HTTP_REFERER'         => 'invali_d',
			'REMOTE_ADDR'          => '192.168.1.100',
			'REQUEST_METHOD'       => 'GET',
			'REQUEST_SCHEME'       => 'http',
			'HTTPS'                => 'on',
			'REQUEST_URI'          => '/blog/posts?order_by=title&order=asc',
			'SERVER_PORT'          => 8080,
			'SERVER_NAME'          => 'domain.tld',
		],
		// Custom
		'HEADERS'  => null,
		'FILES'    => null,
		// Content Negotiation
		'ACCEPT'   => null,
		'CHARSET'  => null,
		'ENCODING' => null,
		'LANGUAGE' => null,
	];
}

