<?php namespace Tests\HTTP\Client;

use Framework\HTTP\Client\Request;
use Framework\HTTP\Cookie;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
	/**
	 * @var Request
	 */
	protected $request;

	protected function setUp()
	{
		$this->request = new Request('http://localhost');
	}

	public function testProtocol()
	{
		$this->assertEquals('HTTP/1.1', $this->request->getProtocol());
		$this->request->setProtocol('HTTP/2.0');
		$this->assertEquals('HTTP/2.0', $this->request->getProtocol());
	}

	public function testMethod()
	{
		$this->assertEquals('GET', $this->request->getMethod());
		$this->request->setMethod('post');
		$this->assertEquals('POST', $this->request->getMethod());
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid HTTP method: FOO');
		$this->request->setMethod('foo');
	}

	public function testURL()
	{
		$this->assertEquals('http://localhost/', (string) $this->request->getURL());
	}

	public function testHeaders()
	{
		$this->assertEquals([], $this->request->getHeaders('foo'));
		$this->assertNull($this->request->getHeader('Foo'));
		$this->request->setHeaders([
			'Foo' => ['Foo'],
			'content-Type' => ['text/html'],
		]);
		$this->assertEquals('Foo', $this->request->getHeader('Foo'));
		$this->assertEquals('text/html', $this->request->getHeader('content-type'));
		$this->request->removeHeader('content-type');
		$this->assertNull($this->request->getHeader('content-type'));
		$this->request->removeHeaders('Foo');
		$this->assertNull($this->request->getHeader('Foo'));
	}

	public function testCookies()
	{
		$this->assertEmpty($this->request->getCookies());
		$this->assertNull($this->request->getHeader('cookie'));
		$this->request->setCookie(new Cookie('session', 'abc123'));
		$this->assertNotEmpty($this->request->getCookies());
		$this->assertEquals('session=abc123', $this->request->getHeader('cookie'));
		$this->assertEquals('abc123', $this->request->getCookie('session')->getValue());
		$this->request->setCookie(new Cookie('foo', 'bar'));
		$this->assertEquals('session=abc123; foo=bar', $this->request->getHeader('cookie'));
		$this->request->removeCookie('session');
		$this->assertEquals('foo=bar', $this->request->getHeader('cookie'));
		$this->request->removeCookies(['foo']);
		$this->assertNull($this->request->getHeader('cookie'));
		$this->request->setCookies([
			new Cookie('j', 'jota'),
			new Cookie('m', 'eme'),
		]);
		$this->assertEquals('j=jota; m=eme', $this->request->getHeader('cookie'));
	}

	public function testBody()
	{
		$this->assertEquals('', $this->request->getBody());
		$this->request->setBody('body');
		$this->assertEquals('body', $this->request->getBody());
		$this->request->setBody(['a' => 1]);
		$this->assertEquals('a=1', $this->request->getBody());
	}
}
