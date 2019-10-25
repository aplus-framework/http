<?php namespace Tests\HTTP\Client;

use Framework\HTTP\Client\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
	/**
	 * @var Response
	 */
	protected $response;

	protected function setUp() : void
	{
		$this->response = new Response(
			'HTTP/1.1',
			200,
			'OK',
			[
				'Foo' => ['Foo'],
				'content-Type' => ['text/html'],
				'set-cookie' => [
					'session_id=35ab1d7a4955d926a3694ab5990c0eb1; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0; path=/admin; domain=localhost; secure; HttpOnly; SameSite=Strict',
					'foo=bar; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0',
				],
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
			'foo' => ['Foo'],
			'content-type' => ['text/html'],
			'set-cookie' => [
				'session_id=35ab1d7a4955d926a3694ab5990c0eb1; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0; path=/admin; domain=localhost; secure; HttpOnly; SameSite=Strict',
				'foo=bar; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0',
			],
		], $this->response->getAllHeaders());
		$this->assertEquals('Foo', $this->response->getHeader('Foo'));
		$this->assertEquals('text/html', $this->response->getHeader('content-type'));
		$this->assertEquals([
			'session_id=35ab1d7a4955d926a3694ab5990c0eb1; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0; path=/admin; domain=localhost; secure; HttpOnly; SameSite=Strict',
			'foo=bar; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0',
		], $this->response->getHeaders('set-cookie'));
		$this->assertEquals(
			'foo=bar; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0',
			$this->response->getHeader('set-cookie')
		);
		$this->assertEquals(
			'foo=bar; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0',
			$this->response->getHeader('set-cookie', 1)
		);
		$this->assertEquals(
			'session_id=35ab1d7a4955d926a3694ab5990c0eb1; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0; path=/admin; domain=localhost; secure; HttpOnly; SameSite=Strict',
			$this->response->getHeader('set-cookie', 0)
		);
		$this->assertNull($this->response->getHeader('set-cookie', 2));
	}

	public function testBody()
	{
		$this->assertEquals('body', $this->response->getBody());
	}

	public function testJson()
	{
		$this->assertFalse($this->response->isJSON());
		$this->assertFalse($this->response->getJSON());
		$this->response = new Response(
			'HTTP/1.1',
			200,
			'OK',
			['content-type' => ['application/json']],
			'{"a":1}'
		);
		$this->assertTrue($this->response->isJSON());
		$this->assertIsObject($this->response->getJSON());
	}

	public function testStatusLine()
	{
		$this->assertEquals('200 OK', $this->response->getStatusLine());
	}
}
