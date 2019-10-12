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

	protected function setUp() : void
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
		$this->expectExceptionMessage('Invalid HTTP Request Method: Foo');
		$this->request->setMethod('Foo');
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
			'content-Type' => 'text/html',
			'custom' => ['a', 'b', 'c'],
		]);
		$this->assertEquals('Foo', $this->request->getHeader('Foo'));
		$this->assertEquals('text/html', $this->request->getHeader('content-type'));
		$this->assertEquals('c', $this->request->getHeader('custom'));
		$this->assertEquals('c', $this->request->getHeader('custom', 2));
		$this->assertEquals('b', $this->request->getHeader('custom', 1));
		$this->request->removeHeader('custom');
		$this->assertNull($this->request->getHeader('custom', 2));
		$this->assertEquals('b', $this->request->getHeader('custom'));
		$this->request->removeHeader('content-type');
		$this->assertNull($this->request->getHeader('content-type'));
		$this->request->removeHeaders('Foo');
		$this->assertNull($this->request->getHeader('Foo'));
		$this->assertEquals([
			'custom' => ['a', 'b'],
		], $this->request->getAllHeaders());
		$this->request->removeAllHeaders();
		$this->assertEquals([], $this->request->getAllHeaders());
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

	public function testContentType()
	{
		$this->assertNull($this->request->getHeader('content-type'));
		$this->request->setContentType('text/html');
		$this->assertEquals('text/html; charset=UTF-8', $this->request->getHeader('content-type'));
	}

	public function testFiles()
	{
		$this->assertFalse($this->request->hasFiles());
		$this->assertEquals('GET', $this->request->getMethod());
		$this->assertEquals([], $this->request->getFiles());
		$this->request->setFiles([__FILE__]);
		$this->assertTrue($this->request->hasFiles());
		$this->assertEquals('POST', $this->request->getMethod());
		$this->assertInstanceOf(\CURLFile::class, $this->request->getFiles()[0]);
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Path does not match a file: /tmp/unknown-00');
		$this->request->setFiles(['/tmp/unknown-00']);
	}

	public function testPOST()
	{
		$this->assertEquals('GET', $this->request->getMethod());
		$this->request->setPOST(['a' => 1, 'b' => 2]);
		$this->assertEquals('POST', $this->request->getMethod());
		$this->assertEquals('a=1&b=2', $this->request->getBody());
	}

	public function testJSON()
	{
		$this->assertNull($this->request->getHeader('content-type'));
		$this->request->setJSON(['a' => 1]);
		$this->assertEquals(
			'application/json; charset=UTF-8',
			$this->request->getHeader('content-type')
		);
		$this->assertEquals('{"a":1}', $this->request->getBody());
	}
}
