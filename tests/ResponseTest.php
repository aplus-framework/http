<?php namespace Tests\HTTP;

use Framework\HTTP\Cookie;
use Framework\HTTP\Request;
use Framework\HTTP\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
	protected Response $response;

	public function setUp() : void
	{
		$this->response = new Response(new RequestMock(['domain.tld']));
	}

	public function testRedirectInstance() : void
	{
		self::assertInstanceOf(Request::class, $this->response->getRequest());
	}

	/**
	 * @see http://en.wikipedia.org/wiki/Post/Redirect/Get
	 * @runInSeparateProcess
	 */
	public function testPostRedirectGet() : void
	{
		RequestMock::setInput(\INPUT_SERVER, [
			'REQUEST_METHOD' => 'POST',
		]);
		$request = new RequestMock(['domain.tld']);
		$this->response = new class($request) extends Response {
		};
		\session_start();
		$this->response->redirect('/new', ['foo']);
		self::assertSame('/new', $this->response->getHeader('Location'));
		self::assertSame(303, $this->response->getStatusCode());
		self::assertSame(['foo'], $request->getRedirectData());
	}

	public function testRedirectDataWithoutSession() : void
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Session must be active to set redirect data');
		$this->response->redirect('/new', ['foo']);
	}

	public function testInvalidRedirectCode() : void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid Redirection code: 404');
		$this->response->redirect('/new', [], 404);
	}

	public function testRedirect() : void
	{
		$this->response->redirect('/new');
		self::assertSame('/new', $this->response->getHeader('Location'));
		self::assertSame(307, $this->response->getStatusCode());
		self::assertSame('Temporary Redirect', $this->response->getStatusReason());
		$this->response->redirect('/other', [], 301);
		self::assertSame('/other', $this->response->getHeader('Location'));
		self::assertSame(301, $this->response->getStatusCode());
		self::assertSame('Moved Permanently', $this->response->getStatusReason());
	}

	public function testBody() : void
	{
		self::assertSame('', $this->response->getBody());
		echo '<p>This will be Lost when call setBody()</p>';
		self::assertSame(
			'<p>This will be Lost when call setBody()</p>',
			$this->response->getBody()
		);
		$this->response->setBody('<h1>Title</h1>');
		self::assertSame(
			'<h1>Title</h1>',
			$this->response->getBody()
		);
		echo 'Foo';
		self::assertSame(
			'<h1>Title</h1>Foo',
			$this->response->getBody()
		);
		$this->response->appendBody(' Appended');
		self::assertSame(
			'<h1>Title</h1>Foo Appended',
			$this->response->getBody()
		);
		$this->response->prependBody('Preprended ');
		self::assertSame(
			'Preprended <h1>Title</h1>Foo Appended',
			$this->response->getBody()
		);
		echo 'Ignored';
		$this->response->setBody('Replace');
		self::assertSame(
			'Replace',
			$this->response->getBody()
		);
	}

	public function testCache() : void
	{
		self::assertNull($this->response->getHeader('Cache-Control'));
		$this->response->setCache(15);
		self::assertSame('private, max-age=15', $this->response->getHeader('Cache-Control'));
		self::assertNotEmpty($this->response->getHeader('Expires'));
		self::assertSame(15, $this->response->getCacheSeconds());
		$this->response->setCache(30, true);
		self::assertSame('public, max-age=30', $this->response->getHeader('Cache-Control'));
		self::assertNotEmpty($this->response->getHeader('Expires'));
		self::assertSame(30, $this->response->getCacheSeconds());
		$this->response->setNoCache();
		self::assertSame(
			'no-cache, no-store, max-age=0',
			$this->response->getHeader('Cache-Control')
		);
		self::assertSame(0, $this->response->getCacheSeconds());
	}

	public function testCookie() : void
	{
		self::assertSame([], $this->response->getCookies());
		$cookie_1 = new Cookie('session_id', 'abc');
		$this->response->setCookie($cookie_1);
		self::assertSame([
			'session_id' => $cookie_1,
		], $this->response->getCookies());
		$cookie_2 = (new Cookie('cart', '123'))->setExpires(3600)->setPath('/')->setSecure(true);
		$this->response->setCookie($cookie_2);
		self::assertSame($cookie_2, $this->response->getCookie('cart'));
		self::assertSame([
			'session_id' => $cookie_1,
			'cart' => $cookie_2,
		], $this->response->getCookies());
		$this->response->removeCookie('cart');
		self::assertNull($this->response->getCookie('cart'));
		self::assertSame([
			'session_id' => $cookie_1,
		], $this->response->getCookies());
		$this->response->removeCookies(['session_id']);
		self::assertEmpty($this->response->getCookies());
		$this->response->setCookies([
			new Cookie('foo', 'foo'),
		]);
		self::assertNotEmpty($this->response->getCookies());
	}

	public function testDate() : void
	{
		self::assertNull($this->response->getHeader('Date'));
		$datetime = new \DateTime('+5 seconds');
		$this->response->setDate($datetime);
		self::assertSame(
			$datetime->format('D, d M Y H:i:s') . ' GMT',
			$this->response->getHeader('Date')
		);
	}

	public function testETag() : void
	{
		self::assertNull($this->response->getHeader('ETag'));
		$this->response->setETag('foo');
		self::assertSame('foo', $this->response->getHeader('ETag'));
	}

	public function testHeader() : void
	{
		self::assertSame([], $this->response->getHeaders());
		$this->response->setHeader('content-type', 'text/html');
		$this->response->setHeader('dnt', 1); // @phpstan-ignore-line
		$this->response->setHeaders([
			'host' => 'http://localhost',
			'ETag' => 'foo',
		]);
		self::assertSame([
			'content-type' => 'text/html',
			'dnt' => '1',
			'host' => 'http://localhost',
			'etag' => 'foo',
		], $this->response->getHeaders());
		$this->response->removeHeader('CONTENT-TYPE');
		$this->response->removeHeader('etag');
		self::assertSame([
			'dnt' => '1',
			'host' => 'http://localhost',
		], $this->response->getHeaders());
		$this->response->removeHeader('dnt');
		self::assertSame([
			'host' => 'http://localhost',
		], $this->response->getHeaders());
		$this->response->setHeaders([ // @phpstan-ignore-line
			'dnt' => 1,
			'x-custom-2' => 'bar',
		]);
		self::assertSame([
			'host' => 'http://localhost',
			'dnt' => '1',
			'x-custom-2' => 'bar',
		], $this->response->getHeaders());
		self::assertSame('1', $this->response->getHeader('dnt'));
		self::assertSame('bar', $this->response->getHeader('X-Custom-2'));
	}

	public function testHeadersAlreadyIsSent() : void
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Headers already is sent');
		$this->response->send();
	}

	public function testInvalidStatusCode() : void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid status code: 900');
		$this->response->setStatusCode(900);
	}

	public function testJSON() : void
	{
		$this->response->setJSON(['test' => 123]);
		self::assertSame('{"test":123}', $this->response->getBody());
		self::assertSame(
			'application/json; charset=UTF-8',
			$this->response->getHeader('Content-Type')
		);
		$this->expectException(\JsonException::class);
		$this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');
		// See: https://php.net/manual/pt_BR/function.json-last-error.php#example-4408
		$this->response->setJSON("\xB1\x31");
	}

	public function testLastModified() : void
	{
		self::assertNull($this->response->getHeader('Last-Modified'));
		$datetime = new \DateTime('+5 seconds');
		$this->response->setLastModified($datetime);
		self::assertSame(
			$datetime->format('D, d M Y H:i:s') . ' GMT',
			$this->response->getHeader('Last-Modified')
		);
	}

	public function testNotModified() : void
	{
		$this->response->setNotModified();
		self::assertSame(
			'304 Not Modified',
			$this->response->getStatusLine()
		);
	}

	/**
	 * @see https://stackoverflow.com/questions/9745080/test-php-headers-with-phpunit
	 *
	 * @runInSeparateProcess
	 */
	public function testSend() : void
	{
		$this->response->setHeader('foo', 'bar');
		$this->response->setCookie(
			(new Cookie('session_id', 'abc123'))->setExpires(\time() + 3600)
		);
		$this->response->setBody('Hello!');
		self::assertFalse($this->response->isSent());
		\ob_start();
		$this->response->send();
		$contents = \ob_get_clean();
		self::assertSame([
			'foo: bar',
			'Date: ' . \gmdate('D, d M Y H:i:s') . ' GMT',
			'Content-Type: text/html; charset=UTF-8',
			'Set-Cookie: session_id=abc123; expires=' . \gmdate('D, d-M-Y H:i:s', \time() + 3600)
			. ' GMT; Max-Age=3600',
		], xdebug_get_headers());
		self::assertSame('Hello!', $contents);
		self::assertTrue($this->response->isSent());
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Response already is sent');
		$this->response->send();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendedBody() : void
	{
		self::assertSame('', $this->response->getBody());
		echo 'foo';
		self::assertSame('foo', $this->response->getBody());
		self::assertSame('foo', $this->response->getBody());
		$this->response->send();
		self::assertSame('foo', $this->response->getBody());
		self::assertSame('foo', $this->response->getBody());
	}

	public function testStatus() : void
	{
		self::assertSame('200 OK', $this->response->getStatusLine());
		self::assertSame(200, $this->response->getStatusCode());
		self::assertSame('OK', $this->response->getStatusReason());
		$this->response->setStatusLine(201);
		self::assertSame('201 Created', $this->response->getStatusLine());
		$this->response->setStatusReason('Other');
		self::assertSame('201 Other', $this->response->getStatusLine());
		$this->response->setStatusLine(483, 'Custom');
		self::assertSame('483 Custom', $this->response->getStatusLine());
	}

	public function testUnknownStatus() : void
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Unknown status code must have a reason: 483');
		$this->response->setStatusLine(483);
	}

	public function testSetContents() : void
	{
		self::assertNull($this->response->getHeader('content-type'));
		$this->response->setContentType('foo');
		self::assertSame('foo; charset=UTF-8', $this->response->getHeader('content-type'));
		self::assertNull($this->response->getHeader('content-language'));
		$this->response->setContentLanguage('de');
		self::assertSame('de', $this->response->getHeader('content-language'));
		self::assertNull($this->response->getHeader('content-encoding'));
		$this->response->setContentEncoding('gzip');
		self::assertSame('gzip', $this->response->getHeader('content-encoding'));
	}
}
