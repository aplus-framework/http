<?php namespace Tests\HTTP\Client;

use Framework\HTTP\Client\Client;
use Framework\HTTP\Client\Request;
use Framework\HTTP\Client\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
	/**
	 * @var Client
	 */
	protected $client;

	protected function setUp() : void
	{
		$this->client = new Client();
	}

	public function testOptions()
	{
		$this->assertEquals([
			\CURLOPT_CONNECTTIMEOUT => 10,
			\CURLOPT_TIMEOUT => 60,
			\CURLOPT_FOLLOWLOCATION => true,
			\CURLOPT_MAXREDIRS => 1,
			\CURLOPT_AUTOREFERER => true,
			\CURLOPT_RETURNTRANSFER => true,
		], $this->client->getOptions());
		$this->client->setOption(\CURLOPT_RETURNTRANSFER, false);
		$this->assertEquals([
			\CURLOPT_CONNECTTIMEOUT => 10,
			\CURLOPT_TIMEOUT => 60,
			\CURLOPT_FOLLOWLOCATION => true,
			\CURLOPT_MAXREDIRS => 1,
			\CURLOPT_AUTOREFERER => true,
			\CURLOPT_RETURNTRANSFER => false,
		], $this->client->getOptions());
		$this->client->reset();
		$this->assertEquals([
			\CURLOPT_CONNECTTIMEOUT => 10,
			\CURLOPT_TIMEOUT => 60,
			\CURLOPT_FOLLOWLOCATION => true,
			\CURLOPT_MAXREDIRS => 1,
			\CURLOPT_AUTOREFERER => true,
			\CURLOPT_RETURNTRANSFER => true,
		], $this->client->getOptions());
	}

	public function testRun()
	{
		$request = new Request('https://www.google.com');
		$request->setHeader('Content-Type', 'text/html');
		$response = $this->client->run($request);
		$this->assertInstanceOf(Response::class, $response);
		$this->assertGreaterThan(100, \strlen($response->getBody()));
		$this->client->setOption(\CURLOPT_RETURNTRANSFER, false);
		$response = $this->client->run($request);
		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals('', $response->getBody());
		$this->assertGreaterThan(100, \strlen(\ob_get_contents()));
		$this->assertArrayHasKey('connect_time', $this->client->getInfo());
	}

	public function testTimeout()
	{
		$this->client->setRequestTimeout(10);
		$this->client->setResponseTimeout(20);
		$this->assertContains([
			\CURLOPT_CONNECTTIMEOUT => 10,
			\CURLOPT_TIMEOUT => 20,
		], $this->client->getOptions());
	}

	public function testProtocols()
	{
		$request = new Request('https://www.google.com');
		$request->setProtocol('HTTP/1.1');
		$this->assertEquals('HTTP/1.1', $request->getProtocol());
		$response = $this->client->run($request);
		$this->assertEquals('HTTP/1.1', $response->getProtocol());
		$this->client->reset();
		$request->setProtocol('HTTP/2.0');
		$this->assertEquals('HTTP/2.0', $request->getProtocol());
		$response = $this->client->run($request);
		$this->assertEquals('HTTP/2', $response->getProtocol());
	}

	public function testMethods()
	{
		$request = new Request('https://www.google.com');
		$request->setMethod('post');
		$this->assertEquals('POST', $request->getMethod());
		$response = $this->client->run($request);
		$this->assertInstanceOf(Response::class, $response);
		$request->setMethod('put');
		$this->assertEquals('PUT', $request->getMethod());
		$response = $this->client->run($request);
		$this->assertInstanceOf(Response::class, $response);
	}

	public function testRunError()
	{
		$request = new Request('http://domain.tld');
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Could not resolve host: domain.tld');
		$this->client->run($request);
	}
}
