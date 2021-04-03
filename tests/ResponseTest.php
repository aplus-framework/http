<?php namespace Tests\HTTP;

use Framework\HTTP\Cookie;
use Framework\HTTP\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
	protected Response $response;

	/**
	 * @runInSeparateProcess
	 */
	public function testPostRedirectGet()
	{
		$request = new RequestMock(['domain.tld']);
		$request->setServerVariable('REQUEST_METHOD', 'POST');
		$this->response = new class($request) extends Response {
		};
		\session_start();
		$this->response->redirect('/new', ['foo']);
		$this->assertEquals('/new', $this->response->getHeader('Location'));
		$this->assertEquals(303, $this->response->getStatusCode());
		$this->assertEquals(['foo'], $request->getRedirectData());
	}

	public function testRedirectDataWithoutSession()
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Session must be active to set redirect data');
		$this->response->redirect('/new', ['foo']);
	}

	public function testInvalidRedirectCode()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid Redirection code: 404');
		$this->response->redirect('/new', [], 404);
	}

	public function testRedirect()
	{
		$this->response->redirect('/new');
		$this->assertEquals('/new', $this->response->getHeader('Location'));
		$this->assertEquals(307, $this->response->getStatusCode());
		$this->response->redirect('/other', [], 301);
		$this->assertEquals('/other', $this->response->getHeader('Location'));
		$this->assertEquals(301, $this->response->getStatusCode());
	}

	public function setUp() : void
	{
		$this->response = new Response(new RequestMock(['domain.tld']));
	}

	public function testBody()
	{
		echo '<p>This will be Lost when call setBody()</p>';
		$this->assertEquals(
			'<p>This will be Lost when call setBody()</p>',
			$this->response->getBody()
		);
		$this->response->setBody('<h1>Title</h1>');
		$this->assertEquals(
			'<h1>Title</h1>',
			$this->response->getBody()
		);
		echo 'Foo';
		$this->assertEquals(
			'<h1>Title</h1>Foo',
			$this->response->getBody()
		);
		$this->response->appendBody(' Appended');
		$this->assertEquals(
			'<h1>Title</h1>Foo Appended',
			$this->response->getBody()
		);
		$this->response->prependBody('Preprended ');
		$this->assertEquals(
			'Preprended <h1>Title</h1>Foo Appended',
			$this->response->getBody()
		);
		echo 'Ignored';
		$this->response->setBody('Replace');
		$this->assertEquals(
			'Replace',
			$this->response->getBody()
		);
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
		$cookie_1 = new Cookie('session_id', 'abc');
		$this->response->setCookie($cookie_1);
		$this->assertEquals([
			'session_id' => $cookie_1,
		], $this->response->getCookies());
		$cookie_2 = (new Cookie('cart', '123'))->setExpires(3600)->setPath('/')->setSecure(true);
		$this->response->setCookie($cookie_2);
		$this->assertEquals($cookie_2, $this->response->getCookie('cart'));
		$this->assertEquals([
			'session_id' => $cookie_1,
			'cart' => $cookie_2,
		], $this->response->getCookies());
		$this->response->removeCookie('cart');
		$this->assertNull($this->response->getCookie('cart'));
		$this->assertEquals([
			'session_id' => $cookie_1,
		], $this->response->getCookies());
		$this->response->removeCookies(['session_id']);
		$this->assertEmpty($this->response->getCookies());
		$this->response->setCookies([
			new Cookie('foo', 'foo'),
		]);
		$this->assertNotEmpty($this->response->getCookies());
	}

	public function testCSRFToken()
	{
		$this->assertNull($this->response->getCookie('X-CSRF-Token'));
		$this->response->setCSRFToken('foo');
		$this->assertEquals(
			'foo',
			$this->response->getCookie('X-CSRF-Token')->getValue()
		);
	}

	public function testGenerateCSRFToken()
	{
		$this->assertEquals(64, \mb_strlen($this->response->generateCSRFToken()));
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
		$this->assertEquals([], $this->response->getHeaders('content-type'));
		$this->response->setHeader('content-type', 'text/html');
		$this->response->setHeader('dnt', 1);
		$this->response->setHeaders([
			'host' => 'http://localhost',
			'ETag' => 'foo',
		]);
		$this->assertEquals([
			'content-type' => 'text/html',
			'dnt' => '1',
			'host' => 'http://localhost',
			'etag' => 'foo',
		], $this->response->getHeaders());
		$this->response->removeHeader('CONTENT-TYPE');
		$this->response->removeHeader('etag');
		$this->assertEquals([
			'dnt' => '1',
			'host' => 'http://localhost',
		], $this->response->getHeaders());
		$this->response->removeHeader('dnt');
		$this->assertEquals([
			'host' => 'http://localhost',
		], $this->response->getHeaders());
		$this->response->setHeaders([
			'dnt' => 1,
			'x-custom-2' => 'bar',
		]);
		$this->assertEquals([
			'dnt' => '1',
			'host' => 'http://localhost',
			'x-custom-2' => 'bar',
		], $this->response->getHeaders());
		$this->assertEquals('1', $this->response->getHeader('dnt'));
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
		$this->response->setCookie(
			(new Cookie('session_id', 'abc123'))->setExpires(\time() + 3600)
		);
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
			. ' GMT; Max-Age=3600',
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

	public function testSetContents()
	{
		$this->assertNull($this->response->getHeader('content-type'));
		$this->response->setContentType('foo');
		$this->assertEquals('foo; charset=UTF-8', $this->response->getHeader('content-type'));
		$this->assertNull($this->response->getHeader('content-language'));
		$this->response->setContentLanguage('de');
		$this->assertEquals('de', $this->response->getHeader('content-language'));
		$this->assertNull($this->response->getHeader('content-encoding'));
		$this->response->setContentEncoding('gzip');
		$this->assertEquals('gzip', $this->response->getHeader('content-encoding'));
	}
}
