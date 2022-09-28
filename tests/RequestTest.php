<?php
/*
 * This file is part of Aplus Framework HTTP Library.
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

    /**
     * @runInSeparateProcess
     */
    public function testForceHttpsWithHttp() : void
    {
        RequestMock::setInput(\INPUT_SERVER, [
            'REQUEST_SCHEME' => 'http',
        ]);
        $request = new RequestMock();
        $request->forceHttps();
        self::assertFalse($request->isSecure());
        $url = $request->getUrl()->setScheme('https');
        self::assertContains('Location: ' . $url, xdebug_get_headers());
        self::assertSame(301, \http_response_code());
    }

    /**
     * @runInSeparateProcess
     */
    public function testForceHttpsWithHttps() : void
    {
        RequestMock::setInput(\INPUT_SERVER, [
            'HTTPS' => 'on',
            'REQUEST_SCHEME' => 'https',
        ]);
        $request = new RequestMock();
        $request->forceHttps();
        self::assertTrue($request->isSecure());
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
                'email' => 'foo@bar.com',
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

    /**
     * @runInSeparateProcess
     */
    public function testToStringMultipart() : void
    {
        RequestMock::$input[\INPUT_SERVER] = [
            'HTTP_CONTENT_TYPE' => 'multipart/form-data; boundary=---------------------------8721656041911415653955004498',
            'HTTP_HOST' => 'domain.tld',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_SCHEME' => 'http',
            'REQUEST_URI' => '/blog/posts',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ];
        RequestMock::$input[\INPUT_POST] = [
            'name' => 'Maria',
            'country' => [
                'city' => 'Porto Alegre',
            ],
        ];
        $filepath = __DIR__ . '/files/file.txt';
        $_FILES = [
            'files' => [
                'name' => [
                    1 => [
                        'aa' => [
                            0 => 'Test.php',
                            1 => '',
                        ],
                    ],
                    2 => 'Other File.php',
                ],
                'full_path' => [
                    1 => [
                        'aa' => [
                            0 => 'Test.php',
                            1 => '',
                        ],
                    ],
                    2 => 'Other File.php',
                ],
                'type' => [
                    1 => [
                        'aa' => [
                            0 => 'application/x-httpd-php',
                            1 => '',
                        ],
                    ],
                    2 => 'text/x-php',
                ],
                'tmp_name' => [
                    1 => [
                        'aa' => [
                            0 => $filepath,
                            1 => '',
                        ],
                    ],
                    2 => $filepath,
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
                            0 => \filesize($filepath),
                            1 => 0,
                        ],
                    ],
                    2 => \filesize($filepath),
                ],
            ],
            'foo' => [
                'name' => '',
                'full_path' => '',
                'type' => '',
                'tmp_name' => '',
                'error' => 4,
                'size' => 0,
            ],
        ];
        $this->request = new RequestMock();
        $startLine = 'POST /blog/posts HTTP/1.1';
        $headerLines = [
            'Content-Type: multipart/form-data; boundary=---------------------------8721656041911415653955004498',
            'Host: domain.tld',
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
        ];
        $fields[0] = [
            'Content-Disposition: form-data; name="name"',
            '',
            'Maria',
        ];
        $fields[1] = [
            'Content-Disposition: form-data; name="country[city]"',
            '',
            'Porto Alegre',
        ];
        $files[0] = [
            'Content-Disposition: form-data; name="files[1][aa][0]"; filename="Test.php"',
            'Content-Type: application/x-httpd-php',
            '',
            \file_get_contents($filepath),
        ];
        $files[1] = [
            'Content-Disposition: form-data; name="files[1][aa][1]"; filename=""',
            'Content-Type: ',
            '',
            '',
        ];
        $files[2] = [
            'Content-Disposition: form-data; name="files[2]"; filename="Other File.php"',
            'Content-Type: text/x-php',
            '',
            \file_get_contents($filepath),
        ];
        $files[3] = [
            'Content-Disposition: form-data; name="foo"; filename=""',
            'Content-Type: ',
            '',
            '',
        ];
        $boundary = '-----------------------------8721656041911415653955004498';
        $body = $boundary . "\r\n";
        $body .= \implode("\r\n", $fields[0]) . "\r\n";
        $body .= $boundary . "\r\n";
        $body .= \implode("\r\n", $fields[1]) . "\r\n";
        $body .= $boundary . "\r\n";
        $body .= \implode("\r\n", $files[0]) . "\r\n";
        $body .= $boundary . "\r\n";
        $body .= \implode("\r\n", $files[1]) . "\r\n";
        $body .= $boundary . "\r\n";
        $body .= \implode("\r\n", $files[2]) . "\r\n";
        $body .= $boundary . "\r\n";
        $body .= \implode("\r\n", $files[3]) . "\r\n";
        $body .= $boundary . "--\r\n";
        $contentLength = \strlen($body);
        self::assertSame(953, $contentLength);
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

    public function testBearerAuth() : void
    {
        $this->request->setHeader(
            'Authorization',
            'Bearer foobar'
        );
        $expected = [
            'token' => 'foobar',
        ];
        self::assertSame($expected, $this->request->getBearerAuth());
        self::assertSame($expected, $this->request->getBearerAuth());
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
        self::assertNull($this->request->getBearerAuth());
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
        self::assertSame([], $this->request->getEnv());
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
                'full_path' => [
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
                'full_path' => '',
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

    public function testGet() : void
    {
        self::assertSame([
            'order_by' => 'title',
            'order' => 'asc',
        ], $this->request->getGet());
        self::assertSame('asc', $this->request->getGet('order'));
        self::assertSame('title', $this->request->getGet('order_by'));
        self::assertNull($this->request->getGet('unknow'));
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
        self::assertSame('192.168.1.100', $this->request->getIp());
    }

    public function testIsAjax() : void
    {
        self::assertTrue($this->request->isAjax());
        self::assertTrue($this->request->isAjax());
    }

    public function testIsSecure() : void
    {
        self::assertFalse($this->request->isSecure());
        self::assertFalse($this->request->isSecure());
    }

    public function testJson() : void
    {
        self::assertFalse($this->request->isJson());
        self::assertFalse($this->request->getJson());
    }

    public function testIsForm() : void
    {
        self::assertFalse($this->request->isForm());
    }

    public function testIsPost() : void
    {
        self::assertFalse($this->request->isPost());
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
        self::assertFalse($this->request->isMethod('post'));
        $this->request->setMethod('post');
        self::assertSame('POST', $this->request->getMethod());
        self::assertTrue($this->request->isMethod('post'));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request method: Foo');
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
                'email' => 'foo@bar.com',
                'city' => 'bar',
            ],
            'csrf_token' => 'foo',
        ], $this->request->getPost());
        self::assertSame('Aw3S0me', $this->request->getPost('password'));
        self::assertSame('phpdev', $this->request->getPost('username'));
        self::assertNull($this->request->getPost('unknow'));
        self::assertFalse($this->request->getPost('unknow', \FILTER_VALIDATE_EMAIL));
        self::assertSame([
            'name' => 'foo',
            'email' => 'foo@bar.com',
            'city' => 'bar',
        ], $this->request->getPost('user'));
        self::assertSame('foo', $this->request->getPost('user[name]'));
        self::assertSame('bar', $this->request->getPost('user[city]'));
        self::assertSame('foo@bar.com', $this->request->getPost('user[email]'));
        self::assertSame(
            'foo@bar.com',
            $this->request->getPost('user[email]', \FILTER_VALIDATE_EMAIL)
        );
        self::assertFalse($this->request->getPost('csrf_token', \FILTER_VALIDATE_EMAIL));
    }

    public function testProxiedIp() : void
    {
        self::assertNull($this->request->getProxiedIp());
        $this->request->setHeader(
            'X-Forwarded-For',
            '192.168.0.10, 2001:db8:85a3:8d3:1319:8a2e:370:7348,192.168.0.22'
        );
        self::assertSame('192.168.0.10', $this->request->getProxiedIp());
        $this->request->setHeader('X-Real-IP', '192.168.0.33');
        self::assertSame('192.168.0.33', $this->request->getProxiedIp());
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

    public function testUrl() : void
    {
        self::assertSame(
            'http://domain.tld/blog/posts?order_by=title&order=asc',
            (string) $this->request->getUrl()
        );
        self::assertInstanceOf(URL::class, $this->request->getUrl());
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
