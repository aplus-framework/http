<?php namespace Tests\HTTP;

use Framework\HTTP\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
	/**
	 * @var RequestProxyMock
	 */
	protected $proxy_request;
	/**
	 * @var RequestMock
	 */
	protected $request;

	public function _testGeoIP()
	{
		$this->assertInstanceOf(\Framework\HTTP\GeoIP::class, $this->request->getGeoIP());
	}

	public function _testUserAgent()
	{
		$this->assertInstanceOf(
			\Framework\HTTP\UserAgent::class,
			$this->request->getUserAgent()
		);
	}

	public function setUp()
	{
		$this->request = new RequestMock();
		$this->proxy_request = new RequestProxyMock();
	}

	public function testAccept()
	{
		$this->assertEquals([], $this->proxy_request->getAccept());
		$this->assertEquals([
			'text/html',
			'application/xhtml+xml',
			'application/xml',
			'*/*',
		], $this->request->getAccept());
		$this->assertEquals('text/html', $this->request->getAccept([
			'text/html',
			'application/xml',
		]));
		$this->assertEquals('text/html', $this->request->getAccept([
			'application/xml',
			'text/html',
		]));
		$this->assertEquals('text/html', $this->request->getAccept([
			'foo',
			'text/html',
		]));
		$this->assertEquals('foo', $this->request->getAccept([
			'foo',
			'bar',
		]));
	}

	public function testBasicAuth()
	{
		$this->request->setInput([
			'SERVER' => [
				'HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('user:pass'),
			],
		]);
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

	public function testCookie()
	{
		$this->assertEquals('cart-123', $this->request->getCookie('cart'));
		$this->assertEquals('abc', $this->request->getCookie('session_id'));
		$this->assertNull($this->request->getCookie('unknown'));
	}

	public function testCookies()
	{
		$this->assertEquals([
			'session_id' => 'abc',
			'cart' => 'cart-123',
			'status-bar' => 'open',
		], $this->request->getCookies());
	}

	public function testDigestAuth()
	{
		$this->request->setInput([
			'SERVER' => [
				'HTTP_AUTHORIZATION' => 'Digest realm="testrealm@host.com", qop="auth,auth-int", nonce="dcd98b7102dd2f0e8b11d0f600bfb0c093", opaque="5ccc069c403ebaf9f0171e9517f40e41"',
			],
		]);
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

	public function testGet()
	{
		$this->assertEquals([
			'order_by' => 'title',
			'order' => 'asc',
		], $this->request->getGET());
		$this->assertEquals('asc', $this->request->getGET('order'));
		$this->assertEquals('title', $this->request->getGET('order_by'));
		$this->assertNull($this->request->getGET('unknow'));
		$this->assertEquals(['order' => 'asc'], $this->request->getGET(['order']));
	}

	public function testHeader()
	{
		$this->assertEquals('abc', $this->request->getHeader('etag'));
	}

	public function testHeaders()
	{
		$this->assertEquals([
			'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Charset' => 'utf-8, iso-8859-1;q=0.5, *;q=0.1',
			'Accept-Encoding' => 'gzip, deflate',
			'Accept-Language' => 'pt-BR,es;q=0.8,en;q=0.5,en-US;q=0.3',
			'Content-Type' => 'text/html; charset=UTF-8',
			'ETag' => 'abc',
			'Referer' => 'http://domain.tld/contact.html',
			'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
			'X-Requested-With' => 'XMLHTTPREQUEST',
		], $this->request->getHeaders());
	}

	public function testHost()
	{
		$this->assertEquals('domain.tld', $this->request->getHost());
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid host: ');
		new Request('');
	}

	public function testIP()
	{
		$this->assertEquals('192.168.1.100', $this->request->getIP());
	}

	public function testIsAJAX()
	{
		$this->assertTrue($this->request->isAJAX());
		$this->assertFalse($this->proxy_request->isAJAX());
	}

	public function testIsSecure()
	{
		$this->assertFalse($this->request->isSecure());
		$this->assertTrue($this->proxy_request->isSecure());
	}

	public function testJSON()
	{
		$this->assertFalse($this->request->getJSON());
		$this->assertEquals(123, $this->proxy_request->getJSON()->test);
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
	}

	public function testPort()
	{
		$this->assertEquals(80, $this->request->getPort());
		$this->assertEquals(8080, $this->proxy_request->getPort());
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
		], $this->request->getPOST());
		$this->assertEquals('Aw3S0me', $this->request->getPOST('password'));
		$this->assertEquals('phpdev', $this->request->getPOST('username'));
		$this->assertNull($this->request->getPOST('unknow'));
		$this->assertEquals(['password' => 'Aw3S0me'], $this->request->getPOST(['password']));
		$this->assertEquals('foo', $this->request->getPOST('user[name]'));
		$this->assertEquals(
			['user[city]' => 'bar', 'username' => 'phpdev'],
			$this->request->getPOST(['user[city]', 'username'])
		);
	}

	public function testProxiedIP()
	{
		$this->assertNull($this->request->getProxiedIP());
		$this->assertEquals('192.168.1.2', $this->proxy_request->getProxiedIP());
	}

	public function testReferer()
	{
		$this->assertEquals('http://domain.tld/contact.html', $this->request->getReferer());
		$this->assertInstanceOf(\Framework\HTTP\URL::class, $this->request->getReferer());
		$this->assertNull($this->proxy_request->getReferer());
	}

	public function testURL()
	{
		$this->assertEquals(
			'http://domain.tld/blog/posts?order_by=title&order=asc',
			$this->request->getURL()
		);
		$this->assertInstanceOf(\Framework\HTTP\URL::class, $this->request->getURL());
		$this->assertEquals(
			'https://real-domain.tld:8080/blog/posts?order_by=title&order=asc',
			$this->proxy_request->getURL()
		);
		$this->assertInstanceOf(\Framework\HTTP\URL::class, $this->proxy_request->getURL());
	}
}
