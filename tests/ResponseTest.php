<?php namespace Tests\HTTP;

use Framework\HTTP\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
	/**
	 * @var Response
	 */
	protected $response;

	public function _testPostRedirectGet()
	{
		$this->response = new class() extends Response {
			protected function getServer(string $name)
			{
				if ($this->input['SERVER'] === null) {
					$this->input['SERVER'] = [
						'REQUEST_METHOD' => 'POST',
						'SERVER_PROTOCOL' => 'HTTP/1.1',
					];
				}
				return $this->input['SERVER'][$name] ?? null;
			}
		};
		$this->response->redirect('/new');
		$this->assertEquals('/new', $this->response->getHeader('Location'));
		$this->assertEquals(303, $this->response->getStatus('code'));
	}

	public function _testRedirect()
	{
		$this->response->redirect('/new');
		$this->assertEquals('/new', $this->response->getHeader('Location'));
		$this->assertEquals(302, $this->response->getStatus('code'));
		$this->response->redirect('/other', 301);
		$this->assertEquals('/other', $this->response->getHeader('Location'));
		$this->assertEquals(301, $this->response->getStatus('code'));
	}

	public function setUp()
	{
		$this->response = new Response(new RequestMock());
	}

	public function testBody()
	{
		// Starts Output Buffer to avoid PHPUnit Test Risk:
		// "Test code or tested code did not (only) close its own output buffers"
		//\ob_start();
		//echo '<p>This will be Lost when call setBody()</p>';
		$this->assertEquals('', $this->response->getBody());
		$this->response->setBody('<h1>Title</h1>');
		//echo '<p>Content</p>';
		$this->assertEquals('<h1>Title</h1>', $this->response->getBody());
		/*if (ob_get_length())
		{
			ob_get_clean();
		}*/
	}

	public function testCache()
	{
		$this->assertNull($this->response->getHeader('Cache-Control'));
		$this->response->setCache(15);
		$this->assertEquals('private, max-age=15', $this->response->getHeader('Cache-Control'));
		$this->assertNotEmpty($this->response->getHeader('Expires'));
		$this->assertEquals(15, $this->response->getCacheSeconds());
		$this->response->setCache(30, true);
		$this->assertEquals('public, max-age=30', $this->response->getHeader('Cache-Control'));
		$this->assertNotEmpty($this->response->getHeader('Expires'));
		$this->assertEquals(30, $this->response->getCacheSeconds());
		$this->response->setNoCache();
		$this->assertEquals(
			'no-cache, no-store, max-age=0',
			$this->response->getHeader('Cache-Control')
		);
		$this->assertEquals(0, $this->response->getCacheSeconds());
	}

	public function testCookie()
	{
		$this->assertEquals([], $this->response->getCookies());
		$this->response->setCookie('session_id', 'abc');
		$this->assertEquals([
			'session_id' => [
				'name' => 'session_id',
				'value' => 'abc',
				'expires' => \time() - 86500,
				'path' => '/',
				'domain' => '',
				'secure' => false,
				'httponly' => false,
				'samesite' => null,
			],
		], $this->response->getCookies());
		$this->response->setCookie('cart', '123', 3600, '', '/', true);
		$this->assertEquals([
			'name' => 'cart',
			'value' => '123',
			'expires' => \time() + 3600,
			'path' => '/',
			'domain' => '',
			'secure' => true,
			'httponly' => false,
			'samesite' => null,
		], $this->response->getCookie('cart'));
		$this->assertEquals([
			'session_id' => [
				'name' => 'session_id',
				'value' => 'abc',
				'expires' => \time() - 86500,
				'path' => '/',
				'domain' => '',
				'secure' => false,
				'httponly' => false,
				'samesite' => null,
			],
			'cart' => [
				'name' => 'cart',
				'value' => '123',
				'expires' => \time() + 3600,
				'path' => '/',
				'domain' => '',
				'secure' => true,
				'httponly' => false,
				'samesite' => null,
			],
		], $this->response->getCookies());
		$this->response->removeCookie('cart');
		$this->assertNull($this->response->getCookie('cart'));
		$this->assertEquals([
			'session_id' => [
				'name' => 'session_id',
				'value' => 'abc',
				'expires' => \time() - 86500,
				'path' => '/',
				'domain' => '',
				'secure' => false,
				'httponly' => false,
				'samesite' => null,
			],
		], $this->response->getCookies());
		$this->response->removeCookies(['session_id']);
		$this->assertEquals([], $this->response->getCookies());
	}

	public function testCSRFToken()
	{
		$this->assertNull($this->response->getCookie('X-CSRF-Token'));
		$this->response->setCSRFToken('foo');
		$this->assertEquals(
			[
				'name' => 'X-CSRF-Token',
				'value' => 'foo',
				'expires' => \time() + 7200,
				'path' => '/',
				'domain' => '',
				'secure' => false,
				'httponly' => true,
				'samesite' => 'Strict',
			],
			$this->response->getCookie('X-CSRF-Token')
		);
	}

	public function testDate()
	{
		$this->assertNull($this->response->getHeader('Date'));
		$datetime = new \DateTime('+5 seconds');
		$this->response->setDate($datetime);
		$this->assertEquals(
			$datetime->format('D, d M Y H:i:s') . ' GMT',
			$this->response->getHeader('Date')
		);
	}

	public function testETag()
	{
		$this->assertNull($this->response->getHeader('ETag'));
		$this->response->setETag('foo');
		$this->assertEquals('foo', $this->response->getHeader('ETag'));
	}

	public function testHeader()
	{
		$this->assertEquals([], $this->response->getHeaders());
		$this->response->setHeader('content-type', 'text/html');
		$this->response->setHeader('dnt', 1);
		$this->response->setHeaders([
			'host' => 'http://localhost',
			'ETag' => 'foo',
		]);
		$this->assertEquals([
			'Content-Type' => 'text/html',
			'DNT' => 1,
			'Host' => 'http://localhost',
			'ETag' => 'foo',
		], $this->response->getHeaders());
		$this->response->removeHeader('CONTENT-TYPE');
		$this->response->removeHeaders(['etag']);
		$this->assertEquals([
			'DNT' => 1,
			'Host' => 'http://localhost',
		], $this->response->getHeaders());
		$this->response->setHeaders([
			'x-custom-1' => 'foo',
			'X-Custom-2' => 'bar',
		]);
		$this->assertEquals([
			'DNT' => 1,
			'Host' => 'http://localhost',
			'x-custom-1' => 'foo',
			'X-Custom-2' => 'bar',
		], $this->response->getHeaders());
		$this->assertEquals('foo', $this->response->getHeader('x-custom-1'));
		$this->assertNull($this->response->getHeader('X-Custom-1'));
		$this->assertEquals('bar', $this->response->getHeader('X-Custom-2'));
	}

	public function testHeadersAlreadyIsSent()
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Headers already is sent');
		$this->response->send();
	}

	public function testInvalidStatusCode()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid status code: 900');
		$this->response->setStatusCode(900);
	}

	public function testJSON()
	{
		$this->response->setJSON(['test' => 123]);
		$this->assertEquals('{"test":123}', $this->response->getBody());
		$this->assertEquals(
			'application/json; charset=UTF-8',
			$this->response->getHeader('Content-Type')
		);
		$this->expectException(\JsonException::class);
		$this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');
		// See: https://php.net/manual/pt_BR/function.json-last-error.php#example-4408
		$this->response->setJSON("\xB1\x31");
	}

	public function testLastModified()
	{
		$this->assertNull($this->response->getHeader('Last-Modified'));
		$datetime = new \DateTime('+5 seconds');
		$this->response->setLastModified($datetime);
		$this->assertEquals(
			$datetime->format('D, d M Y H:i:s') . ' GMT',
			$this->response->getHeader('Last-Modified')
		);
	}

	public function testNotModified()
	{
		$this->response->setNotModified();
		$this->assertEquals(
			'304 Not Modified',
			$this->response->getStatusLine()
		);
	}

	/**
	 * @see https://stackoverflow.com/questions/9745080/test-php-headers-with-phpunit
	 *
	 * @runInSeparateProcess
	 */
	public function testSend()
	{
		$this->response->setHeader('foo', 'bar');
		$this->response->setCookie('session_id', 'abc123', 3600);
		$this->response->setBody('Hello!');
		$this->assertFalse($this->response->isSent());
		\ob_start();
		$this->response->send();
		$contents = \ob_get_clean();
		$this->assertEquals([
			'foo: bar',
			'Date: ' . \gmdate('D, d M Y H:i:s') . ' GMT',
			'Content-Type: text/html; charset=UTF-8',
			'Set-Cookie: session_id=abc123; expires=' . \gmdate('D, d-M-Y H:i:s', \time() + 3600)
			. ' GMT; Max-Age=3600; path=/',
		], xdebug_get_headers());
		$this->assertEquals('Hello!', $contents);
		$this->assertTrue($this->response->isSent());
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Response already is sent');
		$this->response->send();
	}

	public function testStatus()
	{
		$this->assertEquals('200 OK', $this->response->getStatusLine());
		$this->assertEquals(200, $this->response->getStatusCode());
		$this->assertEquals('OK', $this->response->getStatusReason());
		$this->response->setStatusLine(201);
		$this->assertEquals('201 Created', $this->response->getStatusLine());
		$this->response->setStatusReason('Other');
		$this->assertEquals('201 Other', $this->response->getStatusLine());
		$this->response->setStatusLine(483, 'Custom');
		$this->assertEquals('483 Custom', $this->response->getStatusLine());
	}

	public function testUnknownStatus()
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Unknown status code must have a reason: 483');
		$this->response->setStatusLine(483);
	}
}
