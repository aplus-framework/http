<?php namespace Tests\HTTP;

use Framework\HTTP\Request;
use Framework\HTTP\Exceptions\RequestException;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
	/**
	 * @var \Framework\HTTP\Request
	 */
	protected $request;
	/**
	 * @var \Framework\HTTP\Request
	 */
	protected $proxy_request;

	public function setUp()
	{
		$this->request       = new Mocks\Request();
		$this->proxy_request = new Mocks\ProxyRequest();
	}

	public function testReferer()
	{
		$this->assertEquals('http://domain.tld/contact.html', $this->request->getReferrer());
		$this->assertInstanceOf(\Framework\HTTP\URL::class, $this->request->getReferrer(true));

		$this->assertEquals(null, $this->proxy_request->getReferrer());
		$this->assertEquals(null, $this->proxy_request->getReferrer(true));
	}

	public function testMethod()
	{
		$this->assertEquals('GET', $this->request->getMethod());
	}

	public function testHeader()
	{
		$this->assertEquals([
			'Accept'           => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Charset'   => 'utf-8, iso-8859-1;q=0.5, *;q=0.1',
			'Accept-Encoding'  => 'gzip, deflate',
			'Accept-Language'  => 'pt-BR,es;q=0.8,en;q=0.5,en-US;q=0.3',
			'Content-Type'     => 'text/html; charset=UTF-8',
			'ETag'             => 'abc',
			'Referer'          => 'http://domain.tld/contact.html',
			'User-Agent'       => 'Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
			'X-Requested-With' => 'XMLHTTPREQUEST',
		], $this->request->getHeader());

		$this->assertEquals([
			'ETag'       => 'abc',
			'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
		], $this->request->getHeader([
			'etag',
			'user-agent',
		]));

		$this->assertEquals('abc', $this->request->getHeader('etag'));
	}

	public function testBody()
	{
		$this->assertEquals('color=red&height=500px&width=800', $this->request->getBody());
		$this->assertEquals([
			'color'  => 'red',
			'height' => '500px',
			'width'  => '800',
		], $this->request->getBody(true));
	}

	public function testJSON()
	{
		$this->assertEquals(false, $this->request->getJSON());
		$this->assertEquals(123, $this->proxy_request->getJSON()->test);
	}

	public function testIP()
	{
		$this->assertEquals('192.168.1.100', $this->request->getIP());
	}

	public function testProxiedIP()
	{
		$this->assertEquals(null, $this->request->getProxiedIP());
		$this->assertEquals('192.168.1.2', $this->proxy_request->getProxiedIP());
	}

	public function _testGeoIP()
	{
		$this->assertInstanceOf(\Framework\HTTP\GeoIP::class, $this->request->getGeoIP());
	}

	public function testIsAJAX()
	{
		$this->assertEquals(true, $this->request->isAJAX());
		$this->assertEquals(false, $this->request->isAJAX(false));
		$this->assertEquals(true, $this->request->isAJAX(false, 'XMLHTTPREQUEST'));

		$this->assertEquals(false, $this->proxy_request->isAJAX());
		$this->assertEquals(false, $this->proxy_request->isAJAX(false));
		$this->assertEquals(false, $this->proxy_request->isAJAX(false, 'XMLHTTPREQUEST'));
	}

	public function testIsSecure()
	{
		$this->assertEquals(false, $this->request->isSecure());
		$this->assertEquals(true, $this->proxy_request->isSecure());
	}

	public function testPort()
	{
		$this->assertEquals(80, $this->request->getPort());
		$this->assertEquals(8080, $this->proxy_request->getPort());
	}

	public function testHost()
	{
		$this->assertEquals('domain.tld', $this->request->getHost());

		$this->expectException(RequestException::class);

		(new Request(''));
	}

	public function testURL()
	{
		$this->assertEquals(
			'http://domain.tld/blog/posts?order_by=title&order=asc',
			$this->request->getURL()
		);
		$this->assertInstanceOf(\Framework\HTTP\URL::class, $this->request->getURL(true));

		$this->assertEquals(
			'https://real-domain.tld:8080/blog/posts?order_by=title&order=asc',
			$this->proxy_request->getURL()
		);
		$this->assertInstanceOf(\Framework\HTTP\URL::class, $this->proxy_request->getURL(true));
	}

	public function _testUserAgent()
	{
		$this->assertEquals('Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
			$this->request->getUserAgent());

		$this->assertInstanceOf(\Framework\HTTP\UserAgent::class,
			$this->request->getUserAgent(true));
	}

	public function testPost()
	{
		$this->assertEquals([
			'username' => 'phpdev',
			'password' => 'Aw3S0me',
		], $this->request->getPOST());

		$this->assertEquals('Aw3S0me', $this->request->getPOST('password'));
		$this->assertEquals('phpdev', $this->request->getPOST('username'));
		$this->assertEquals(null, $this->request->getPOST('unknow'));
		$this->assertEquals(['password' => 'Aw3S0me'], $this->request->getPOST(['password']));
	}

	public function testGet()
	{
		$this->assertEquals([
			'order_by' => 'title',
			'order'    => 'asc',
		], $this->request->getGET());

		$this->assertEquals('asc', $this->request->getGET('order'));
		$this->assertEquals('title', $this->request->getGET('order_by'));
		$this->assertEquals(null, $this->request->getGET('unknow'));
		$this->assertEquals(['order' => 'asc'], $this->request->getGET(['order']));
	}

	public function testCookie()
	{
		$this->assertEquals([
			'session_id' => 'abc',
			'cart'       => 'cart-123',
			'status-bar' => 'open',
		], $this->request->getCookie());

		$this->assertEquals('cart-123', $this->request->getCookie('cart'));
		$this->assertEquals('abc', $this->request->getCookie('session_id'));
		$this->assertEquals(null, $this->request->getCookie('unknow'));
		$this->assertEquals(['status-bar' => 'open', 'session_id' => 'abc'],
			$this->request->getCookie(['session_id', 'status-bar']));
	}

	public function testFiles()
	{
		$_FILES = [
			'file' =>
				[
					'name'     =>
						[
							1 =>
								[
									'aa' =>
										[
											0 => 'Screen Shot 2018-10-28 at 04.53.13.png',
											1 => '',
										],
								],
							2 => 'Screen Shot 2018-10-28 at 04.51.13.png',
						],
					'type'     =>
						[
							1 =>
								[
									'aa' =>
										[
											0 => 'image/png',
											1 => '',
										],
								],
							2 => 'image/png',
						],
					'tmp_name' =>
						[
							1 =>
								[
									'aa' =>
										[
											0 => '/tmp/phpP0AhMI',
											1 => '',
										],
								],
							2 => '/tmp/phpK5PJNm',
						],
					'error'    =>
						[
							1 =>
								[
									'aa' =>
										[
											0 => 0,
											1 => 4,
										],
								],
							2 => 0,
						],
					'size'     =>
						[
							1 =>
								[
									'aa' =>
										[
											0 => 41706,
											1 => 0,
										],
								],
							2 => 62820,
						],
				],
			'foo'  =>
				[
					'name'     => '',
					'type'     => '',
					'tmp_name' => '',
					'error'    => 4,
					'size'     => 0,
				],
		];

		$this->assertIsArray($this->request->getFiles());
		$this->assertInstanceOf(
			\Framework\HTTP\UploadedFile::class, $this->request->getFiles('file')[1]['aa'][0]
		);
		$this->assertInstanceOf(
			\Framework\HTTP\UploadedFile::class, $this->request->getFiles('file')[1]['aa'][1]
		);
		$this->assertInstanceOf(
			\Framework\HTTP\UploadedFile::class, $this->request->getFiles('file')[2]
		);
		$this->assertInstanceOf(
			\Framework\HTTP\UploadedFile::class, $this->request->getFiles('foo')
		);
	}

	public function testEnv()
	{
		$this->assertEquals([], $this->request->getENV());
	}

	public function testEtag()
	{
		$this->assertEquals('abc', $this->request->getETag());
	}

	public function testContentType()
	{
		$this->assertEquals('text/html; charset=UTF-8', $this->request->getContentType());
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

	public function testLanguage()
	{
		$this->assertEquals([
			'pt-br',
			'es',
			'en',
			'en-us',
		], $this->request->getLanguage());

		$this->assertEquals('pt-br', $this->request->getLanguage([
			'pt-br',
			'en',
		]));

		$this->assertEquals('pt-br', $this->request->getLanguage([
			'en',
			'pt-br',
		]));

		$this->assertEquals('pt-br', $this->request->getLanguage([
			'foo',
			'pt-br',
		]));

		$this->assertEquals('foo', $this->request->getLanguage([
			'foo',
			'bar',
		]));
	}

	public function testEncoding()
	{
		$this->assertEquals([
			'gzip',
			'deflate',
		], $this->request->getEncoding());

		$this->assertEquals('gzip', $this->request->getEncoding([
			'gzip',
			'deflate',
		]));

		$this->assertEquals('gzip', $this->request->getEncoding([
			'deflate',
			'gzip',
		]));

		$this->assertEquals('deflate', $this->request->getEncoding([
			'foo',
			'deflate',
		]));

		$this->assertEquals('foo', $this->request->getEncoding([
			'foo',
			'bar',
		]));
	}

	public function testCharset()
	{
		$this->assertEquals([
			'utf-8',
			'iso-8859-1',
			'*',
		], $this->request->getCharset());

		$this->assertEquals('utf-8', $this->request->getCharset([
			'utf-8',
			'*',
		]));

		$this->assertEquals('utf-8', $this->request->getCharset([
			'*',
			'utf-8',
		]));

		$this->assertEquals('iso-8859-1', $this->request->getCharset([
			'foo',
			'iso-8859-1',
		]));

		$this->assertEquals('foo', $this->request->getCharset([
			'foo',
			'bar',
		]));
	}
}
