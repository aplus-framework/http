<?php namespace Tests\HTTP;

use Framework\HTTP\Cookie;
use PHPUnit\Framework\TestCase;

class CookieTest extends TestCase
{
	/**
	 * @var \Framework\HTTP\Cookie
	 */
	protected $cookie;

	protected function setUp()
	{
		$this->cookie = new Cookie('foo', 'bar');
	}

	public function testDomain()
	{
		$this->assertNull($this->cookie->getDomain());
		$this->cookie->setDomain('domain.tld');
		$this->assertEquals('domain.tld', $this->cookie->getDomain());
		$this->cookie->setDomain(null);
		$this->assertNull($this->cookie->getDomain());
	}

	public function testExpires()
	{
		$this->assertNull($this->cookie->getExpires());
		$this->cookie->setExpires('+5 seconds');
		$this->assertInstanceOf(\DateTime::class, $this->cookie->getExpires());
		$this->assertEquals(\time() + 5, $this->cookie->getExpires()->format('U'));
		$this->cookie->setExpires($time = \time() + 30);
		$this->assertInstanceOf(\DateTime::class, $this->cookie->getExpires());
		$this->assertEquals($time, $this->cookie->getExpires()->format('U'));
		$this->cookie->setExpires($time = new \DateTime('-5 hours'));
		$this->assertInstanceOf(\DateTime::class, $this->cookie->getExpires());
		$this->assertEquals(\time() - 5 * 60 * 60, $this->cookie->getExpires()->format('U'));
		$this->cookie->setExpires(null);
		$this->assertNull($this->cookie->getExpires());
		$this->expectException(\Exception::class);
		$this->cookie->setExpires('foo');
	}

	public function testHttpOnly()
	{
		$this->assertFalse($this->cookie->isHttpOnly());
		$this->cookie->setHttpOnly();
		$this->assertTrue($this->cookie->isHttpOnly());
		$this->cookie->setHttpOnly(false);
		$this->assertFalse($this->cookie->isHttpOnly());
	}

	public function testName()
	{
		$this->assertEquals('foo', $this->cookie->getName());
		$this->cookie->setName('session_id');
		$this->assertEquals('session_id', $this->cookie->getName());
	}

	public function testPath()
	{
		$this->assertNull($this->cookie->getPath());
		$this->cookie->setPath('/blog');
		$this->assertEquals('/blog', $this->cookie->getPath());
		$this->cookie->setPath(null);
		$this->assertNull($this->cookie->getPath());
	}

	public function testSameSite()
	{
		$this->assertNull($this->cookie->getSameSite());
		$this->cookie->setSameSite('sTrIcT');
		$this->assertEquals('Strict', $this->cookie->getSameSite());
		$this->cookie->setSameSite('lAx');
		$this->assertEquals('Lax', $this->cookie->getSameSite());
		$this->cookie->setSameSite('uNsEt');
		$this->assertEquals('Unset', $this->cookie->getSameSite());
		$this->cookie->setSameSite(null);
		$this->assertNull($this->cookie->getSameSite());
		$this->expectException(\InvalidArgumentException::class);
		$this->cookie->setSameSite('foo');
	}

	public function testSecure()
	{
		$this->assertFalse($this->cookie->isSecure());
		$this->cookie->setSecure();
		$this->assertTrue($this->cookie->isSecure());
		$this->cookie->setSecure(false);
		$this->assertFalse($this->cookie->isSecure());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSend()
	{
		$this->assertTrue($this->cookie->send());
		$this->assertEquals(['Set-Cookie: foo=bar'], \xdebug_get_headers());
		(new Cookie('foo', 'abc123'))->setSecure()->setHttpOnly()->send();
		(new Cookie('foo', 'abc123'))->setExpires('+5 seconds')->send();
		$this->assertEquals([
			'Set-Cookie: foo=bar',
			'Set-Cookie: foo=abc123; secure; HttpOnly',
			'Set-Cookie: foo=abc123; expires='
			. \date('D, d-M-Y H:i:s', \time() + 5) . ' GMT; Max-Age=5',
		], \xdebug_get_headers());
		$this->cookie->setDomain('domain.tld')
			->setPath('/blog')
			->setSecure()
			->setHttpOnly()
			->setSameSite('strict')
			->setValue('baz')
			->setExpires('+30 seconds')
			->send();
		$this->assertContains(
			'Set-Cookie: ' . $this->cookie->getAsString(),
			\xdebug_get_headers()
		);
	}

	public function testString()
	{
		$time = \time() + 30;
		$expected = 'foo=baz; expires='
			. \date('D, d-M-Y H:i:s', $time)
			. ' GMT; Max-Age=30; path=/blog; domain=domain.tld; secure; HttpOnly; SameSite=Strict';
		$this->cookie->setDomain('domain.tld')
			->setPath('/blog')
			->setSecure()
			->setHttpOnly()
			->setSameSite('strict')
			->setValue('baz')
			->setExpires($time);
		$this->assertEquals($expected, $this->cookie->getAsString());
		$this->assertEquals($expected, (string) $this->cookie);
		$this->assertInstanceOf(Cookie::class, $this->cookie);
	}

	public function testValue()
	{
		$this->assertEquals('bar', $this->cookie->getValue());
		$this->cookie->setValue(12345);
		$this->assertEquals('12345', $this->cookie->getValue());
	}
}
