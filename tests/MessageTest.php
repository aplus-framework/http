<?php namespace Tests\HTTP;

use Framework\HTTP\Cookie;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
	protected MessageMock $message;

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
		$this->assertFalse($this->message->hasHeader('from'));
		$this->assertNull($this->message->getHeader('from'));
		$this->message->setHeader('from', 'foo@localhost');
		$this->assertTrue($this->message->hasHeader('from'));
		$this->assertEquals('foo@localhost', $this->message->getHeader('from'));
		$this->assertEquals(['from' => 'foo@localhost'], $this->message->getHeaders());
		$this->message->setHeader('from', 'bar@localhost');
		$this->assertEquals('bar@localhost', $this->message->getHeader('from'));
		$this->message->removeHeader('FROM');
		$this->assertNull($this->message->getHeader('from'));
	}

	public function testHeaders()
	{
		$this->assertEquals([], $this->message->getHeaders());
		$this->message->setHeader('from', 'foo@localhost');
		$this->assertEquals(
			['from' => 'foo@localhost'],
			$this->message->getHeaders()
		);
		$this->message->setHeaders([
			'content-type' => 'application/json',
			'allow' => '*',
		]);
		$this->assertEquals(
			[
				'from' => 'foo@localhost',
				'content-type' => 'application/json',
				'allow' => '*',
			],
			$this->message->getHeaders()
		);
		$this->message->removeHeaders();
		$this->assertEquals([], $this->message->getHeaders());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendHeaders()
	{
		$this->message->setHeader('from', 'foo@localhost');
		$this->message->setHeader('from', 'bar@localhost');
		$this->message->setHeaders([
			'content-type' => 'application/json',
			'allow' => '*',
		]);
		$this->message->sendHeaders();
		$this->assertEquals([
			'From: bar@localhost',
			'Content-Type: application/json',
			'Allow: *',
		], xdebug_get_headers());
	}

	public function testBody()
	{
		$this->assertNull($this->message->getBody());
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
