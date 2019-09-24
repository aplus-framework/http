<?php namespace Tests\HTTP;

use Framework\HTTP\Response;
use PHPUnit\Framework\TestCase;

class ResponseDownloadTest extends TestCase
{
	/**
	 * @var Response
	 */
	protected $response;
	/**
	 * @var RequestMock
	 */
	protected $request;

	protected function setUp() : void
	{
		$this->request = new RequestMock();
		$this->request->setHeader('Range', 'bytes=0-499');
		$this->response = new class($this->request) extends Response {
		};
	}

	public function testInvalidFilepath()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->response->setDownload(__DIR__ . '/unknown');
	}

	public function testInvalidRequestHeader()
	{
		$this->request->setHeader('Range', 'xx');
		$this->response->setDownload(__FILE__);
		$this->assertEquals(416, $this->response->getStatusCode());
		$this->assertStringStartsWith('*/', $this->response->getHeader('Content-Range'));
	}

	public function testSingleByteRanges()
	{
		$this->response->setDownload(__FILE__);
		$this->assertEquals(206, $this->response->getStatusCode());
		$this->assertEquals('text/x-php', $this->response->getHeader('Content-Type'));
		$this->assertEquals('500', $this->response->getHeader('Content-Length'));
		$this->assertStringStartsWith('bytes 0-499/', $this->response->getHeader('Content-Range'));
	}

	public function testMultiByteRanges()
	{
		$this->request->setHeader('Range', 'bytes=0-499,500-549');
		$this->response->setDownload(__FILE__);
		$this->assertEquals(206, $this->response->getStatusCode());
		$this->assertStringStartsWith(
			'multipart/x-byteranges; boundary=',
			$this->response->getHeader('Content-Type')
		);
		$this->assertEquals('822', $this->response->getHeader('Content-Length'));
	}

	public function testInvalidHeaderValue()
	{
		$this->request->setHeader('Range', 'x');
		$this->response->setDownload(__FILE__);
		$this->assertEquals(416, $this->response->getStatusCode());
	}

	public function testInvalidRange()
	{
		$this->request->setHeader('Range', 'bytes=');
		$this->response->setDownload(__FILE__);
		$this->assertEquals(416, $this->response->getStatusCode());
		$this->request->setHeader('Range', 'bytes=-');
		$this->response->setDownload(__FILE__);
		$this->assertEquals(416, $this->response->getStatusCode());
		$this->request->setHeader('Range', 'bytes=a-');
		$this->response->setDownload(__FILE__);
		$this->assertEquals(416, $this->response->getStatusCode());
		$this->request->setHeader('Range', 'bytes=0-y');
		$this->response->setDownload(__FILE__);
		$this->assertEquals(416, $this->response->getStatusCode());
		$this->request->setHeader('Range', 'bytes=0-10000');
		$this->response->setDownload(__FILE__);
		$this->assertEquals(416, $this->response->getStatusCode());
	}

	public function testRange()
	{
		$this->request->setHeader('Range', 'bytes=0-10');
		$this->response->setDownload(__FILE__);
		$this->assertEquals(206, $this->response->getStatusCode());
		$this->request->setHeader('Range', 'bytes=0-10,11-19,25-,-98');
		$this->response->setDownload(__FILE__);
		$this->assertEquals(206, $this->response->getStatusCode());
	}

	public function testHasDownload()
	{
		$this->assertFalse($this->response->hasDownload());
		$this->response->setDownload(__FILE__);
		$this->assertTrue($this->response->hasDownload());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendSinglePart()
	{
		$this->request->setHeader('Range', 'bytes=0-9');
		$this->response->setDownload(__FILE__);
		\ob_start();
		$this->response->send();
		\ob_end_clean();
		$this->assertTrue($this->response->isSent());
		$this->assertStringStartsWith('bytes 0-9/', $this->response->getHeader('Content-Range'));
		$this->assertStringEqualsFile(__FILE__, $this->response->getBody());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendMultiPart()
	{
		$this->request->setHeader('Range', 'bytes=0-9,10-19');
		$this->response->setDownload(__FILE__);
		\ob_start();
		$this->response->send();
		\ob_end_clean();
		$this->assertTrue($this->response->isSent());
		$this->assertStringStartsWith(
			'multipart/x-byteranges; boundary=',
			$this->response->getHeader('Content-Type')
		);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testReadFile()
	{
		$this->response->setDownload(__FILE__, false, false);
		\ob_start();
		$this->response->send();
		\ob_end_clean();
		$this->assertTrue($this->response->isSent());
	}
}
