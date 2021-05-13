<?php namespace Tests\HTTP;

use PHPUnit\Framework\TestCase;

class RequestProxyTest extends TestCase
{
	protected RequestProxyMock $proxyRequest;

	public function setUp() : void
	{
		$this->proxyRequest = new RequestProxyMock([
			'real-domain.tld:8080',
		]);
	}

	public function testHost()
	{
		$this->assertEquals('real-domain.tld', $this->proxyRequest->getHost());
	}

	public function testAccept()
	{
		$this->assertEquals([], $this->proxyRequest->getAccepts());
	}

	public function testIsAJAX()
	{
		$this->assertFalse($this->proxyRequest->isAJAX());
		$this->assertFalse($this->proxyRequest->isAJAX());
	}

	public function testIsSecure()
	{
		$this->assertTrue($this->proxyRequest->isSecure());
		$this->assertTrue($this->proxyRequest->isSecure());
	}

	public function testJSON()
	{
		$this->proxyRequest->setBody('{"test":123}');
		$this->assertEquals(123, $this->proxyRequest->getJSON()->test);
	}

	public function testPort()
	{
		$this->assertEquals(8080, $this->proxyRequest->getPort());
	}

	public function testProxiedIP()
	{
		$this->assertEquals('192.168.1.2', $this->proxyRequest->getProxiedIP());
	}

	public function testReferer()
	{
		$this->assertNull($this->proxyRequest->getReferer());
	}

	public function testURL()
	{
		$this->assertEquals(
			'https://real-domain.tld:8080/blog/posts?order_by=title&order=asc',
			(string) $this->proxyRequest->getURL()
		);
		$this->assertInstanceOf(\Framework\HTTP\URL::class, $this->proxyRequest->getURL());
	}
}
