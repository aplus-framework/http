<?php namespace Tests\HTTP;

use Framework\HTTP\Cookie;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
	/**
	 * @var MessageMock
	 */
	protected $message;

	protected function setUp() : void
	{
		$this->message = new MessageMock();
	}

	public function testProtocol()
	{
		$this->assertEquals('HTTP/1.1', $this->message->getProtocol());
		$this->message->setProtocol('HTTP/2');
		$this->assertEquals('HTTP/2', $this->message->getProtocol());
	}

	public function testHeader()
	{
		$this->assertNull($this->message->getHeader('from'));
		$this->assertEquals(0, $this->message->countHeader('from'));
		$this->message->setHeader('from', 'foo@localhost');
		$this->assertEquals(1, $this->message->countHeader('from'));
		$this->assertEquals('foo@localhost', $this->message->getHeader('from'));
		$this->assertEquals(['foo@localhost'], $this->message->getHeaders('from'));
		$this->message->setHeader('from', 'bar@localhost');
		$this->assertEquals('bar@localhost', $this->message->getHeader('from'));
		$this->assertEquals(['bar@localhost'], $this->message->getHeaders('from'));
		$this->message->addHeader('from', 'baz@localhost');
		$this->assertEquals(['bar@localhost', 'baz@localhost'], $this->message->getHeaders('from'));
		$this->message->setHeader('from', 'foo@localhost', 'bar@localhost');
		$this->assertEquals('bar@localhost', $this->message->getHeader('from'));
		$this->assertEquals('foo@localhost', $this->message->getHeader('from', 0));
		$this->assertEquals(['foo@localhost', 'bar@localhost'], $this->message->getHeaders('from'));
		$this->message->removeHeader('from');
		$this->assertEquals('foo@localhost', $this->message->getHeader('from'));
		$this->message->removeHeaders('FROM');
		$this->assertNull($this->message->getHeader('from'));
	}

	public function testHeaders()
	{
		$this->assertEquals([], $this->message->getAllHeaders());
		$this->message->setHeader('from', 'foo@localhost', 'bar@localhost');
		$this->assertEquals(
			['from' => ['foo@localhost', 'bar@localhost']],
			$this->message->getAllHeaders()
		);
		$this->message->setHeaders([
			'content-type' => ['application/json', 'text/html'],
			'allow' => ['*'],
		]);
		$this->assertEquals(
			[
				'from' => ['foo@localhost', 'bar@localhost'],
				'content-type' => ['application/json', 'text/html'],
				'allow' => ['*'],
			],
			$this->message->getAllHeaders()
		);
		$this->assertEquals(
			[
				'from' => ['foo@localhost', 'bar@localhost'],
				'content-type' => ['application/json', 'text/html'],
				'allow' => ['*'],
			],
			$this->message->getAllHeaders()
		);
		$this->message->removeAllHeaders();
		$this->assertEquals([], $this->message->getAllHeaders());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendHeaders()
	{
		$this->message->setHeader('from', 'foo@localhost', 'bar@localhost');
		$this->message->setHeaders([
			'content-type' => ['application/json'],
			'allow' => ['*'],
		]);
		$this->message->sendHeaders();
		$this->assertEquals([
			'From: foo@localhost',
			'From: bar@localhost',
			'Content-Type: application/json',
			'Allow: *',
		], xdebug_get_headers());
	}

	public function testBody()
	{
		$this->assertEquals('', $this->message->getBody());
		$this->message->setBody('hello');
		$this->assertEquals('hello', $this->message->getBody());
	}

	public function testCookie()
	{
		$this->assertEquals([], $this->message->getCookies());
		$this->assertNull($this->message->getCookie('session'));
		$this->assertFalse($this->message->hasCookie('session'));
		$this->message->setCookie(new Cookie('session', 'abc123'));
		$this->assertTrue($this->message->hasCookie('session'));
		$this->message->setCookie(new Cookie('custom', 'foo'));
		$this->assertEquals('abc123', $this->message->getCookie('session')->getValue());
		$this->assertEquals(['session', 'custom'], \array_keys($this->message->getCookies()));
		$this->message->removeCookies([
			'session',
			'custom',
		]);
		$this->assertEquals([], $this->message->getCookies());
		$this->message->setCookies([new Cookie('other', 'foo')]);
		$this->assertEquals(['other'], \array_keys($this->message->getCookies()));
	}
}
