<?php
/*
 * This file is part of The Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\HTTP;

use Framework\HTTP\UploadedFile;
use Framework\HTTP\URL;
use Framework\HTTP\UserAgent;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    protected RequestMock $request;

    public function setUp() : void
    {
        $this->request = new RequestMock([
            'domain.tld',
        ]);
    }

    public function testInvalidFilterInputType() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid input type: 6');
        $this->request->filterInput(6);
    }

    public function testGetInputWithFilter() : void
    {
        self::assertSame(
            'http://domain.tld/contact.html',
            $this->request->filterInput(\INPUT_SERVER, 'HTTP_REFERER', \FILTER_VALIDATE_URL)
        );
        self::assertFalse(
            $this->request->filterInput(\INPUT_SERVER, 'HTTP_USER_AGENT', \FILTER_VALIDATE_URL)
        );
    }

    public function testUserAgent() : void
    {
        self::assertInstanceOf(
            UserAgent::class,
            $this->request->getUserAgent()
        );
        self::assertInstanceOf(
            UserAgent::class,
            $this->request->getUserAgent()
        );
        $this->request->userAgent = false;
        $this->request->removeHeader('User-Agent');
        self::assertNull($this->request->getUserAgent());
    }

    public function testHost() : void
    {
        self::assertSame('domain.tld', $this->request->getHost());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid host: a_b');
        $this->request->setHost('a_b');
    }

    public function testInvalidHost() : void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid Host: domain.tld');
        $this->request = new RequestMock([
            'other.tld',
        ]);
    }

    public function testAccept() : void
    {
        self::assertSame([
            'text/html',
            'application/xhtml+xml',
            'application/xml',
            '*/*',
        ], $this->request->getAccepts());
        self::assertSame('text/html', $this->request->negotiateAccept([
            'text/html',
            'application/xml',
        ]));
        self::assertSame('text/html', $this->request->negotiateAccept([
            'application/xml',
            'text/html',
        ]));
        self::assertSame('text/html', $this->request->negotiateAccept([
            'foo',
            'text/html',
        ]));
        self::assertSame('foo', $this->request->negotiateAccept([
            'foo',
            'bar',
        ]));
    }

    public function testBasicAuth() : void
    {
        $this->request->setHeader(
            'Authorization',
            'Basic ' . \base64_encode('user:pass')
        );
        $expected = [
            'username' => 'user',
            'password' => 'pass',
        ];
        self::assertSame($expected, $this->request->getBasicAuth());
        self::assertSame($expected, $this->request->getBasicAuth());
    }

    public function testBody() : void
    {
        self::assertSame('', $this->request->getBody());
        // @phpstan-ignore-next-line
        $this->request->setBody('color=red&height=500px&width=800');
        self::assertSame('color=red&height=500px&width=800', $this->request->getBody());
        self::assertSame([], $this->request->getParsedBody());
        $this->request->parsedBody = null;
        $this->request->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        self::assertSame([
            'color' => 'red',
            'height' => '500px',
            'width' => '800',
        ], $this->request->getParsedBody());
        self::assertSame([
            'color' => 'red',
            'height' => '500px',
            'width' => '800',
        ], $this->request->getParsedBody());
        self::assertSame('red', $this->request->getParsedBody('color', \FILTER_SANITIZE_STRING));
        self::assertSame(800, $this->request->getParsedBody('width', \FILTER_VALIDATE_INT));
        self::assertFalse($this->request->getParsedBody('height', \FILTER_VALIDATE_INT));
        $this->request->setMethod('POST');
        self::assertSame([
            'username' => 'phpdev',
            'password' => 'Aw3S0me',
            'user' => [
                'name' => 'foo',
                'city' => 'bar',
            ],
            'csrf_token' => 'foo',
        ], $this->request->getParsedBody());
        $this->request->setMethod('GET');
        // @phpstan-ignore-next-line
        $this->request->setBody('');
        $this->request->parsedBody = [];
        self::assertSame('', $this->request->getBody());
        self::assertSame([], $this->request->getParsedBody());
    }

    public function testCharset() : void
    {
        self::assertSame([
            'utf-8',
            'iso-8859-1',
            '*',
        ], $this->request->getCharsets());
        self::assertSame('utf-8', $this->request->negotiateCharset([
            'utf-8',
            '*',
        ]));
        self::assertSame('utf-8', $this->request->negotiateCharset([
            '*',
            'utf-8',
        ]));
        self::assertSame('iso-8859-1', $this->request->negotiateCharset([
            'foo',
            'iso-8859-1',
        ]));
        self::assertSame('foo', $this->request->negotiateCharset([
            'foo',
            'bar',
        ]));
    }

    public function testContentType() : void
    {
        self::assertSame('text/html; charset=UTF-8', $this->request->getContentType());
    }

    public function testProtocol() : void
    {
        self::assertSame('HTTP/1.1', $this->request->getProtocol());
    }

    public function testStartLine() : void
    {
        self::assertSame(
            'GET /blog/posts?order_by=title&order=asc HTTP/1.1',
            $this->request->getStartLine()
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testToString() : void
    {
        $startLine = 'GET /blog/posts?order_by=title&order=asc HTTP/1.1';
        $headerLines = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Encoding: gzip, deflate',
            'Accept-Language: pt-BR,es;q=0.8,en;q=0.5,en-US;q=0.3',
            'Accept-Charset: utf-8, iso-8859-1;q=0.5, *;q=0.1',
            'Content-Type: text/html; charset=UTF-8',
            'Host: domain.tld',
            'Referer: http://domain.tld/contact.html',
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
            'X-Request-ID: abc123',
            'X-Requested-With: XMLHTTPREQUEST',
        ];
        $body = '';
        $message = $startLine . "\r\n"
            . \implode("\r\n", $headerLines) . "\r\n"
            . "\r\n"
            . $body;
        self::assertSame($message, (string) $this->request);
    }

    public function testCookie() : void
    {
        self::assertSame('cart-123', $this->request->getCookie('cart')->getValue());
        self::assertSame('abc', $this->request->getCookie('session_id')->getValue());
        self::assertNull($this->request->getCookie('unknown'));
    }

    public function testCookies() : void
    {
        self::assertIsArray($this->request->getCookies());
    }

    public function testDigestAuth() : void
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
        self::assertSame($expected, $this->request->getDigestAuth());
    }

    public function testEmptyAuth() : void
    {
        self::assertNull($this->request->getBasicAuth());
        self::assertNull($this->request->getDigestAuth());
    }

    public function testEncoding() : void
    {
        self::assertSame([
            'gzip',
            'deflate',
        ], $this->request->getEncodings());
        self::assertSame('gzip', $this->request->negotiateEncoding([
            'gzip',
            'deflate',
        ]));
        self::assertSame('gzip', $this->request->negotiateEncoding([
            'deflate',
            'gzip',
        ]));
        self::assertSame('deflate', $this->request->negotiateEncoding([
            'foo',
            'deflate',
        ]));
        self::assertSame('foo', $this->request->negotiateEncoding([
            'foo',
            'bar',
        ]));
    }

    public function testEnv() : void
    {
        self::assertSame([], $this->request->getENV());
    }

    public function testFiles() : void
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
        self::assertIsArray($this->request->getFiles());
        self::assertInstanceOf(
            UploadedFile::class,
            // @phpstan-ignore-next-line
            $this->request->getFiles()['file'][1]['aa'][0]
        );
        self::assertInstanceOf(
            UploadedFile::class,
            $this->request->getFile('file[1][aa][0]')
        );
        self::assertInstanceOf(
            UploadedFile::class,
            // @phpstan-ignore-next-line
            $this->request->getFiles()['file'][2]
        );
        self::assertInstanceOf(
            UploadedFile::class,
            $this->request->getFile('foo')
        );
    }

    public function testEmptyFiles() : void
    {
        $_FILES = [];
        $this->request = new RequestMock([
            'domain.tld',
        ]);
        self::assertEmpty($this->request->getFiles());
    }

    public function testQueries() : void
    {
        self::assertSame([
            'order_by' => 'title',
            'order' => 'asc',
        ], $this->request->getQuery());
        self::assertSame('asc', $this->request->getQuery('order'));
        self::assertSame('title', $this->request->getQuery('order_by'));
        self::assertNull($this->request->getQuery('unknow'));
    }

    public function testHeader() : void
    {
        self::assertSame('domain.tld', $this->request->getHeader('host'));
    }

    public function testHeaders() : void
    {
        self::assertSame([
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'accept-encoding' => 'gzip, deflate',
            'accept-language' => 'pt-BR,es;q=0.8,en;q=0.5,en-US;q=0.3',
            'accept-charset' => 'utf-8, iso-8859-1;q=0.5, *;q=0.1',
            'content-type' => 'text/html; charset=UTF-8',
            'host' => 'domain.tld',
            'referer' => 'http://domain.tld/contact.html',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
            'x-request-id' => 'abc123',
            'x-requested-with' => 'XMLHTTPREQUEST',
        ], $this->request->getHeaders());
    }

    public function testIP() : void
    {
        self::assertSame('192.168.1.100', $this->request->getIP());
    }

    public function testIsAJAX() : void
    {
        self::assertTrue($this->request->isAJAX());
        self::assertTrue($this->request->isAJAX());
    }

    public function testIsSecure() : void
    {
        self::assertFalse($this->request->isSecure());
        self::assertFalse($this->request->isSecure());
    }

    public function testJSON() : void
    {
        self::assertFalse($this->request->isJSON());
        self::assertFalse($this->request->getJSON());
    }

    public function testIsForm() : void
    {
        self::assertFalse($this->request->isForm());
    }

    public function testIsPOST() : void
    {
        self::assertFalse($this->request->isPOST());
    }

    public function testHasFiles() : void
    {
        self::assertFalse($this->request->hasFiles());
    }

    public function testLanguage() : void
    {
        self::assertSame([
            'pt-br',
            'es',
            'en',
            'en-us',
        ], $this->request->getLanguages());
        self::assertSame('pt-br', $this->request->negotiateLanguage([
            'pt-br',
            'en',
        ]));
        self::assertSame('pt-br', $this->request->negotiateLanguage([
            'en',
            'pt-br',
        ]));
        self::assertSame('pt-br', $this->request->negotiateLanguage([
            'foo',
            'pt-br',
        ]));
        self::assertSame('foo', $this->request->negotiateLanguage([
            'foo',
            'bar',
        ]));
    }

    public function testMethod() : void
    {
        self::assertSame('GET', $this->request->getMethod());
        $this->request->setMethod('post');
        self::assertSame('POST', $this->request->getMethod());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HTTP Request Method: Foo');
        $this->request->setMethod('Foo');
    }

    public function testPort() : void
    {
        self::assertSame(80, $this->request->getPort());
    }

    public function testPost() : void
    {
        self::assertSame([
            'username' => 'phpdev',
            'password' => 'Aw3S0me',
            'user' => [
                'name' => 'foo',
                'city' => 'bar',
            ],
            'csrf_token' => 'foo',
        ], $this->request->getPOST());
        self::assertSame('Aw3S0me', $this->request->getPOST('password'));
        self::assertSame('phpdev', $this->request->getPOST('username'));
        self::assertNull($this->request->getPOST('unknow'));
        //self::assertSame(['password' => 'Aw3S0me'], $this->request->getPOST(['password']));
        self::assertSame('foo', $this->request->getPOST('user[name]'));
        /*self::assertSame(
            ['user[city]' => 'bar', 'username' => 'phpdev'],
            $this->request->getPOST(['user[city]', 'username'])
        );*/
    }

    public function testProxiedIP() : void
    {
        self::assertNull($this->request->getProxiedIP());
    }

    public function testReferer() : void
    {
        self::assertSame('http://domain.tld/contact.html', (string) $this->request->getReferer());
        self::assertInstanceOf(URL::class, $this->request->getReferer());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRedirectDataEmpty() : void
    {
        \session_start();
        self::assertNull($this->request->getRedirectData());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRedirectData() : void
    {
        \session_start();
        $_SESSION['$']['redirect_data'] = ['foo' => ['bar' => 'baz']];
        self::assertSame($_SESSION['$']['redirect_data'], $this->request->getRedirectData());
        self::assertFalse(isset($_SESSION['$']['redirect_data']));
        self::assertSame(['bar' => 'baz'], $this->request->getRedirectData('foo'));
        self::assertSame('baz', $this->request->getRedirectData('foo[bar]'));
        self::assertNull($this->request->getRedirectData('foo[baz]'));
    }

    public function testRedirectDataWithoutSession() : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Session must be active to get redirect data');
        $this->request->getRedirectData();
    }

    public function testURL() : void
    {
        self::assertSame(
            'http://domain.tld/blog/posts?order_by=title&order=asc',
            (string) $this->request->getURL()
        );
        self::assertInstanceOf(URL::class, $this->request->getURL());
    }

    public function testId() : void
    {
        self::assertSame('abc123', $this->request->getId());
        self::assertSame('abc123', $this->request->getId());
    }

    public function testCallMethodNotAllowed() : void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Method not allowed: prepareStatusLine');
        $this->request->prepareStatusLine(); // @phpstan-ignore-line
    }

    public function testCallMethodNotFound() : void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Method not found: fooBar');
        $this->request->fooBar(); // @phpstan-ignore-line
    }
}
