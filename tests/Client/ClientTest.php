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
	}
}
