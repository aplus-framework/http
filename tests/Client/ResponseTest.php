<?php namespace Tests\HTTP\Client;

use Framework\HTTP\Client\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
	/**
	 * @var Response
	 */
	protected $response;

	protected function setUp()
	{
		$this->response = new Response(
			'HTTP/1.1',
			200,
			'OK',
			[
				'Foo' => 'Foo',
				'content-Type' => 'text/html',
				'set-cookie' => 'session_id=35ab1d7a4955d926a3694ab5990c0eb1; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0; path=/admin; domain=localhost; secure; HttpOnly; SameSite=Strict',
			],
			'body'
		);
	}

	public function testProtocol()
	{
		$this->assertEquals('HTTP/1.1', $this->response->getProtocol());
	}

	public function testStatusCode()
	{
		$this->assertEquals(200, $this->response->getStatusCode());
	}

	public function testStatusReason()
	{
		$this->assertEquals('OK', $this->response->getStatusReason());
	}

	public function testHeaders()
	{
		$this->assertEquals([
			'Foo' => 'Foo',
			'Content-Type' => 'text/html',
			'Set-Cookie' => 'session_id=35ab1d7a4955d926a3694ab5990c0eb1; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0; path=/admin; domain=localhost; secure; HttpOnly; SameSite=Strict',
		], $this->response->getHeaders());
		$this->assertNull($this->response->getHeader('foo'));
		$this->assertEquals('Foo', $this->response->getHeader('Foo'));
		$this->assertEquals('text/html', $this->response->getHeader('content-type'));
	}

	public function testBody()
	{
		$this->assertEquals('body', $this->response->getBody());
	}

	public function testJson()
	{
		$this->assertFalse($this->response->getJSON());
	}
}
