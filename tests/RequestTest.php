<?php namespace Tests\HTTP;

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
	protected RequestProxyMock $proxyRequest;
	protected RequestMock $request;

	public function setUp() : void
	{
		$this->request = new RequestMock([
			'domain.tld',
		]);
		$this->proxyRequest = new RequestProxyMock([
			'real-domain.tld:8080',
		]);
	}

	public function testUserAgent()
	{
		$this->assertInstanceOf(
			\Framework\HTTP\UserAgent::class,
			$this->request->getUserAgent()
		);
		$this->assertInstanceOf(
			\Framework\HTTP\UserAgent::class,
			$this->request->getUserAgent()
		);
		$this->request->userAgent = null;
		$this->request->setServerVariable('HTTP_USER_AGENT', null);
		$this->assertNull($this->request->getUserAgent());
	}

	public function testHost()
	{
		$this->assertEquals('domain.tld', $this->request->getHost());
		$this->assertEquals('real-domain.tld', $this->proxyRequest->getHost());
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid host: a_b');
		$this->request->setHost('a_b');
	}

	public function testInvalidHost()
	{
		$this->expectException(\UnexpectedValueException::class);
		$this->expectExceptionMessage('Invalid Host: domain.tld');
		$this->request = new RequestMock([
			'other.tld',
		]);
	}

	public function testAccept()
	{
		$this->assertEquals([], $this->proxyRequest->getAccepts());
		$this->assertEquals([
			'text/html',
			'application/xhtml+xml',
			'application/xml',
			'*/*',
		], $this->request->getAccepts());
		$this->assertEquals('text/html', $this->request->negotiateAccept([
			'text/html',
			'application/xml',
		]));
		$this->assertEquals('text/html', $this->request->negotiateAccept([
			'application/xml',
			'text/html',
		]));
		$this->assertEquals('text/html', $this->request->negotiateAccept([
			'foo',
			'text/html',
		]));
		$this->assertEquals('foo', $this->request->negotiateAccept([
			'foo',
			'bar',
		]));
	}

	public function testBasicAuth()
	{
		$this->request->setHeader(
			'Authorization',
			'Basic ' . \base64_encode('user:pass')
		);
		$expected = [
			'username' => 'user',
			'password' => 'pass',
		];
		$this->assertEquals($expected, $this->request->getBasicAuth());
		$this->assertEquals($expected, $this->request->getBasicAuth());
	}

	public function testBody()
	{
		$this->assertEquals('color=red&height=500px&width=800', $this->request->getBody());
		$this->assertEquals([
			'color' => 'red',
			'height' => '500px',
			'width' => '800',
		], $this->request->getParsedBody());
		$this->assertEquals([
			'color' => 'red',
			'height' => '500px',
			'width' => '800',
		], $this->request->getParsedBody());
		$this->assertEquals('red', $this->request->getParsedBody('color', \FILTER_SANITIZE_STRING));
		$this->request->setMethod('POST');
		$this->assertEquals([
			'username' => 'phpdev',
			'password' => 'Aw3S0me',
			'user' => [
				'name' => 'foo',
				'city' => 'bar',
			],
			'csrf_token' => 'foo',
		], $this->request->getParsedBody());
		$this->request->setMethod('GET');
		$this->request->body = '';
		$this->request->parsedBody = [];
		$this->assertEquals('', $this->request->getBody());
		$this->assertEquals([], $this->request->getParsedBody());
	}

	public function testCharset()
	{
		$this->assertEquals([
			'utf-8',
			'iso-8859-1',
			'*',
		], $this->request->getCharsets());
		$this->assertEquals('utf-8', $this->request->negotiateCharset([
			'utf-8',
			'*',
		]));
		$this->assertEquals('utf-8', $this->request->negotiateCharset([
			'*',
			'utf-8',
		]));
		$this->assertEquals('iso-8859-1', $this->request->negotiateCharset([
			'foo',
			'iso-8859-1',
		]));
		$this->assertEquals('foo', $this->request->negotiateCharset([
			'foo',
			'bar',
		]));
	}

	public function testContentType()
	{
		$this->assertEquals('text/html; charset=UTF-8', $this->request->getContentType());
	}

	public function testProtocol()
	{
		$this->assertEquals('HTTP/1.1', $this->request->getProtocol());
	}

	public function testCookie()
	{
		$this->assertEquals('cart-123', $this->request->getCookie('cart')->getValue());
		$this->assertEquals('abc', $this->request->getCookie('session_id')->getValue());
		$this->assertNull($this->request->getCookie('unknown'));
	}

	public function testCookies()
	{
		$this->assertIsArray($this->request->getCookies());
	}

	public function testDigestAuth()
	{
		$this->request->setHeader(
			'Authorization',
			'Digest realm="testrealm@host.com", qop="auth,auth-int", nonce="dcd98b7102dd2f0e8b11d0f600bfb0c093", opaque="5ccc069c403ebaf9f0171e9517f40e41"'
		);
		$expected = [
			'username' => null,
			'realm' => 'testrealm@host.com',
			'nonce' => 'dcd98b7102dd2f0e8b11d0f600bfb0c093',
			'uri' => null,
			'response' => null,
			'opaque' => '5ccc069c403ebaf9f0171e9517f40e41',
			'qop' => 'auth,auth-int',
			'nc' => null,
			'cnonce' => null,
		];
		$this->assertEquals($expected, $this->request->getDigestAuth());
	}

	public function testEmptyAuth()
	{
		$this->assertNull($this->request->getBasicAuth());
		$this->assertNull($this->request->getDigestAuth());
	}

	public function testEncoding()
	{
		$this->assertEquals([
			'gzip',
			'deflate',
		], $this->request->getEncodings());
		$this->assertEquals('gzip', $this->request->negotiateEncoding([
			'gzip',
			'deflate',
		]));
		$this->assertEquals('gzip', $this->request->negotiateEncoding([
			'deflate',
			'gzip',
		]));
		$this->assertEquals('deflate', $this->request->negotiateEncoding([
			'foo',
			'deflate',
		]));
		$this->assertEquals('foo', $this->request->negotiateEncoding([
			'foo',
			'bar',
		]));
	}

	public function testEnv()
	{
		$this->assertEquals([], $this->request->getENV());
	}

	public function testEtag()
	{
		$this->assertEquals('abc', $this->request->getETag());
	}

	public function testFiles()
	{
		$_FILES = [
			'file' => [
				'name' => [
					1 => [
						'aa' => [
							0 => 'Screen Shot 2018-10-28 at 04.53.13.png',
							1 => '',
						],
					],
					2 => 'Screen Shot 2018-10-28 at 04.51.13.png',
				],
				'type' => [
					1 => [
						'aa' => [
							0 => 'image/png',
							1 => '',
						],
					],
					2 => 'image/png',
				],
				'tmp_name' => [
					1 => [
						'aa' => [
							0 => '/tmp/phpP0AhMI',
							1 => '',
						],
					],
					2 => '/tmp/phpK5PJNm',
				],
				'error' => [
					1 => [
						'aa' => [
							0 => 0,
							1 => 4,
						],
					],
					2 => 0,
				],
				'size' => [
					1 => [
						'aa' => [
							0 => 41706,
							1 => 0,
						],
					],
					2 => 62820,
				],
			],
			'foo' => [
				'name' => '',
				'type' => '',
				'tmp_name' => '',
				'error' => 4,
				'size' => 0,
			],
		];
		$this->request = new RequestMock();
		$this->assertIsArray($this->request->getFiles());
		$this->assertInstanceOf(
			\Framework\HTTP\UploadedFile::class,
			$this->request->getFiles()['file'][1]['aa'][0]
		);
		$this->assertInstanceOf(
			\Framework\HTTP\UploadedFile::class,
			$this->request->getFile('file[1][aa][0]')
		);
		$this->assertInstanceOf(
			\Framework\HTTP\UploadedFile::class,
			$this->request->getFiles()['file'][2]
		);
		$this->assertInstanceOf(
			\Framework\HTTP\UploadedFile::class,
			$this->request->getFile('foo')
		);
	}

	public function testEmptyFiles()
	{
		$_FILES = [];
		$this->request = new RequestMock([
			'domain.tld',
		]);
		$this->assertEmpty($this->request->getFiles());
	}

	public function testQueries()
	{
		$this->assertEquals([
			'order_by' => 'title',
			'order' => 'asc',
		], $this->request->getQuery());
		$this->assertEquals('asc', $this->request->getQuery('order'));
		$this->assertEquals('title', $this->request->getQuery('order_by'));
		$this->assertNull($this->request->getQuery('unknow'));
		//$this->assertEquals(['order' => 'asc'], $this->request->getGET(['order']));
	}

	public function testHeader()
	{
		$this->assertEquals('abc', $this->request->getHeader('etag'));
	}

	public function testHeaders()
	{
		$this->assertEquals([
			'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'accept-charset' => 'utf-8, iso-8859-1;q=0.5, *;q=0.1',
			'accept-encoding' => 'gzip, deflate',
			'accept-language' => 'pt-BR,es;q=0.8,en;q=0.5,en-US;q=0.3',
			'content-type' => 'text/html; charset=UTF-8',
			'etag' => 'abc',
			'host' => 'domain.tld',
			'referer' => 'http://domain.tld/contact.html',
			'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
			'x-requested-with' => 'XMLHTTPREQUEST',
		], $this->request->getHeaders());
	}

	public function testIP()
	{
		$this->assertEquals('192.168.1.100', $this->request->getIP());
	}

	public function testIsAJAX()
	{
		$this->assertTrue($this->request->isAJAX());
		$this->assertTrue($this->request->isAJAX());
		$this->assertFalse($this->proxyRequest->isAJAX());
		$this->assertFalse($this->proxyRequest->isAJAX());
	}

	public function testIsSecure()
	{
		$this->assertFalse($this->request->isSecure());
		$this->assertFalse($this->request->isSecure());
		$this->assertTrue($this->proxyRequest->isSecure());
		$this->assertTrue($this->proxyRequest->isSecure());
	}

	public function testJSON()
	{
		$this->assertFalse($this->request->isJSON());
		$this->assertFalse($this->request->getJSON());
		$this->assertEquals(123, $this->proxyRequest->getJSON()->test);
	}

	public function testIsForm()
	{
		$this->assertFalse($this->request->isForm());
	}

	public function testIsPOST()
	{
		$this->assertFalse($this->request->isPOST());
	}

	public function testHasFiles()
	{
		$this->assertFalse($this->request->hasFiles());
	}

	public function testLanguage()
	{
		$this->assertEquals([
			'pt-br',
			'es',
			'en',
			'en-us',
		], $this->request->getLanguages());
		$this->assertEquals('pt-br', $this->request->negotiateLanguage([
			'pt-br',
			'en',
		]));
		$this->assertEquals('pt-br', $this->request->negotiateLanguage([
			'en',
			'pt-br',
		]));
		$this->assertEquals('pt-br', $this->request->negotiateLanguage([
			'foo',
			'pt-br',
		]));
		$this->assertEquals('foo', $this->request->negotiateLanguage([
			'foo',
			'bar',
		]));
	}

	public function testMethod()
	{
		$this->assertEquals('GET', $this->request->getMethod());
		$this->request->setMethod('post');
		$this->assertEquals('POST', $this->request->getMethod());
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid HTTP Request Method: Foo');
		$this->request->setMethod('Foo');
	}

	public function testPort()
	{
		$this->assertEquals(80, $this->request->getPort());
		$this->assertEquals(8080, $this->proxyRequest->getPort());
	}

	public function testPost()
	{
		$this->assertEquals([
			'username' => 'phpdev',
			'password' => 'Aw3S0me',
			'user' => [
				'name' => 'foo',
				'city' => 'bar',
			],
			'csrf_token' => 'foo',
		], $this->request->getPOST());
		$this->assertEquals('Aw3S0me', $this->request->getPOST('password'));
		$this->assertEquals('phpdev', $this->request->getPOST('username'));
		$this->assertNull($this->request->getPOST('unknow'));
		//$this->assertEquals(['password' => 'Aw3S0me'], $this->request->getPOST(['password']));
		$this->assertEquals('foo', $this->request->getPOST('user[name]'));
		/*$this->assertEquals(
			['user[city]' => 'bar', 'username' => 'phpdev'],
			$this->request->getPOST(['user[city]', 'username'])
		);*/
	}

	public function testProxiedIP()
	{
		$this->assertNull($this->request->getProxiedIP());
		$this->assertEquals('192.168.1.2', $this->proxyRequest->getProxiedIP());
	}

	public function testReferer()
	{
		$this->assertEquals('http://domain.tld/contact.html', $this->request->getReferer());
		$this->assertInstanceOf(\Framework\HTTP\URL::class, $this->request->getReferer());
		$this->assertNull($this->proxyRequest->getReferer());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRedirectDataEmpty()
	{
		\session_start();
		$this->assertNull($this->request->getRedirectData());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testRedirectData()
	{
		\session_start();
		$_SESSION['$__REDIRECT'] = ['foo' => ['bar' => 'baz']];
		$this->assertEquals($_SESSION['$__REDIRECT'], $this->request->getRedirectData());
		$this->assertFalse(isset($_SESSION['$__REDIRECT']));
		$this->assertEquals(['bar' => 'baz'], $this->request->getRedirectData('foo'));
	}

	public function testRedirectDataWithoutSession()
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Session must be active to get redirect data');
		$this->request->getRedirectData();
	}

	public function testURL()
	{
		$this->assertEquals(
			'http://domain.tld/blog/posts?order_by=title&order=asc',
			(string) $this->request->getURL()
		);
		$this->assertInstanceOf(\Framework\HTTP\URL::class, $this->request->getURL());
		$this->assertEquals(
			'https://real-domain.tld:8080/blog/posts?order_by=title&order=asc',
			(string) $this->proxyRequest->getURL()
		);
		$this->assertInstanceOf(\Framework\HTTP\URL::class, $this->proxyRequest->getURL());
	}

	public function testId()
	{
		$id = $this->request->getId();
		$this->assertIsString($id);
		$this->assertEquals($id, $this->request->getId());
	}
}
