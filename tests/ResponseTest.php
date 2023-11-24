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

use Framework\HTTP\Cookie;
use Framework\HTTP\Request;
use Framework\HTTP\Response;
use Framework\HTTP\Status;
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
     *
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
        self::assertSame(Status::SEE_OTHER, $this->response->getStatusCode());
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
        $this->response->redirect('/new', [], Status::NOT_FOUND);
    }

    public function testRedirect() : void
    {
        $this->response->redirect('/new');
        self::assertSame('/new', $this->response->getHeader('Location'));
        self::assertSame(Status::TEMPORARY_REDIRECT, $this->response->getStatusCode());
        self::assertSame('Temporary Redirect', $this->response->getStatusReason());
        $this->response->redirect('/other', [], Status::MOVED_PERMANENTLY);
        self::assertSame('/other', $this->response->getHeader('Location'));
        self::assertSame(Status::MOVED_PERMANENTLY, $this->response->getStatusCode());
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

    public function testEtag() : void
    {
        self::assertNull($this->response->getHeader('ETag'));
        $this->response->setEtag('foo');
        self::assertSame('"foo"', $this->response->getHeader('ETag'));
        $this->response->setEtag('bar', false);
        self::assertSame('W/"bar"', $this->response->getHeader('ETag'));
    }

    public function testAutoEtag() : void
    {
        self::assertFalse($this->response->isAutoEtag());
        $this->response->setAutoEtag();
        self::assertTrue($this->response->isAutoEtag());
        $this->response->setAutoEtag(false);
        self::assertFalse($this->response->isAutoEtag());
    }

    /**
     * @runInSeparateProcess
     */
    public function testAutoEtagNegotiationWithGet() : void
    {
        $body = '<h1>Hello world!</h1>';
        $etag = '"' . \hash('xxh3', $body) . '"';
        RequestMock::setInput(\INPUT_SERVER, [
            'REQUEST_METHOD' => 'GET',
            'HTTP_IF_NONE_MATCH' => $etag,
        ]);
        $this->response = new Response(new RequestMock(['domain.tld']));
        $this->response->setAutoEtag();
        $this->response->setBody($body);
        \ob_start();
        $this->response->send();
        \ob_end_clean();
        self::assertSame((string) \strlen($body), $this->response->getHeader('Content-Length'));
        self::assertSame($etag, $this->response->getHeader('ETag'));
        self::assertSame('304 Not Modified', $this->response->getStatus());
        self::assertSame('', $this->response->getBody());
    }

    /**
     * @runInSeparateProcess
     */
    public function testAutoEtagNegotiationWithPost() : void
    {
        $body = '<h1>My names is Aplus!</h1>';
        $etag = '"' . \hash('xxh3', $body) . '"';
        RequestMock::setInput(\INPUT_SERVER, [
            'REQUEST_METHOD' => 'POST',
            'HTTP_IF_MATCH' => '"etag-that-do-not-match"',
        ]);
        $this->response = new Response(new RequestMock(['domain.tld']));
        $this->response->setAutoEtag();
        $this->response->setBody($body);
        \ob_start();
        $this->response->send();
        \ob_end_clean();
        self::assertSame((string) \strlen($body), $this->response->getHeader('Content-Length'));
        self::assertSame($etag, $this->response->getHeader('ETag'));
        self::assertSame('412 Precondition Failed', $this->response->getStatus());
        self::assertSame('', $this->response->getBody());
    }

    /**
     * @runInSeparateProcess
     */
    public function testAutoEtagHashAlgo() : void
    {
        $body = '<h1>I have many names!</h1>';
        $etag = '"' . \hash('crc32', $body) . '"';
        $this->response = new Response(new RequestMock(['domain.tld']));
        $this->response->setBody($body);
        $this->response->setAutoEtag(hashAlgo: 'crc32');
        \ob_start();
        $this->response->send();
        \ob_end_clean();
        self::assertSame($etag, $this->response->getHeader('ETag'));
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
        $this->response->appendHeader('dnt', 'ahaa');
        self::assertSame('1, ahaa', $this->response->getHeader('dnt'));
        self::assertSame('bar', $this->response->getHeader('X-Custom-2'));
        $this->response->removeHeaders();
        self::assertSame([], $this->response->getHeaders());
    }

    public function testHeadersAreAlreadySent() : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Headers are already sent');
        $this->response->send();
    }

    public function testInvalidStatusCode() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid response status code: 900');
        $this->response->setStatusCode(900);
    }

    public function testJson() : void
    {
        $this->response->setJson(['test' => 123]);
        self::assertSame('{"test":123}', $this->response->getBody());
        self::assertSame(
            'application/json; charset=UTF-8',
            $this->response->getHeader('Content-Type')
        );
        $this->expectException(\JsonException::class);
        $this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');
        // See: https://php.net/manual/pt_BR/function.json-last-error.php#example-4408
        $this->response->setJson("\xB1\x31");
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
            $this->response->getStatus()
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
        $xdebugCookieDateFormat = \PHP_VERSION_ID < 80200
            ? 'D, d-M-Y H:i:s'
            : 'D, d M Y H:i:s';
        self::assertSame([
            'foo: bar',
            'Date: ' . \gmdate('D, d M Y H:i:s') . ' GMT',
            'Content-Type: text/html; charset=UTF-8',
            'Set-Cookie: session_id=abc123; expires='
            . \gmdate($xdebugCookieDateFormat, \time() + 3600) . ' GMT; Max-Age=3600',
        ], xdebug_get_headers());
        self::assertSame('Hello!', $contents);
        self::assertTrue($this->response->isSent());
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Response is already sent');
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
        \ob_start();
        $this->response->send();
        \ob_end_clean();
        self::assertSame('foo', $this->response->getBody());
        self::assertSame('foo', $this->response->getBody());
    }

    public function testStatus() : void
    {
        self::assertSame('200 OK', $this->response->getStatus());
        self::assertSame(Status::OK, $this->response->getStatusCode());
        self::assertSame('OK', $this->response->getStatusReason());
        self::assertFalse($this->response->isStatusCode(201));
        $this->response->setStatus(Status::CREATED);
        self::assertTrue($this->response->isStatusCode(201));
        self::assertSame('201 Created', $this->response->getStatus());
        $this->response->setStatusReason('Other');
        self::assertSame('201 Other', $this->response->getStatus());
        $this->response->setStatus(483, 'Custom');
        self::assertTrue($this->response->isStatusCode(483));
        self::assertSame('483 Custom', $this->response->getStatus());
    }

    public function testStartLine() : void
    {
        self::assertSame(
            'HTTP/1.1 200 OK',
            $this->response->getStartLine()
        );
    }

    public function testToString() : void
    {
        $startLine = 'HTTP/1.1 200 OK';
        $headerLines = [
            'Date: ' . \gmdate(\DATE_RFC7231),
            'Content-Type: text/html; charset=UTF-8',
        ];
        $body = <<<'HTML'
            <!doctype html>
            <html lang="en">
            <head>
                <title>Message Body</title>
            <body>
                <p>Hello, <strong>A+</strong>!</p>
            </body>
            </head>
            </html>
            HTML;
        $this->response->setBody($body);
        $message = $startLine . "\r\n"
            . \implode("\r\n", $headerLines) . "\r\n"
            . "\r\n"
            . $body;
        self::assertSame($message, (string) $this->response);
    }

    public function testToStringWithDownload() : void
    {
        $filename = __DIR__ . '/files/file.txt';
        $body = (string) \file_get_contents($filename);
        $length = \strlen($body);
        $startLine = 'HTTP/1.1 200 OK';
        $headerLines = [
            'Last-Modified: ' . \gmdate(\DATE_RFC7231, (int) \filemtime($filename)),
            'Content-Disposition: attachment; filename="file.txt"',
            'Accept-Ranges: bytes',
            'Content-Length: ' . $length,
            'Content-Type: text/plain',
            'Date: ' . \gmdate(\DATE_RFC7231),
        ];
        $this->response->setDownload($filename);
        $message = $startLine . "\r\n"
            . \implode("\r\n", $headerLines) . "\r\n"
            . "\r\n"
            . $body;
        self::assertSame($message, (string) $this->response);
    }

    public function testToStringWithDownloadMultipart() : void
    {
        RequestMock::setInput(\INPUT_SERVER, [
            'HTTP_RANGE' => 'bytes=0-1,4-',
        ]);
        $this->response = new Response(new RequestMock());
        $filename = __DIR__ . '/files/file.txt';
        $boundary = \md5($filename);
        $body = "\r\n--{$boundary}--\r\n"
            . "Content-Type: application/octet-stream\r\n"
            . "Content-Range: bytes 0-1/11\r\n"
            . "\r\n"
            . 'Hi'
            . "\r\n--{$boundary}--\r\n"
            . "Content-Type: application/octet-stream\r\n"
            . "Content-Range: bytes 4-10/11\r\n"
            . "\r\n"
            . "Aplus!\n"
            . "\r\n--{$boundary}--\r\n";
        $length = \strlen($body);
        $startLine = 'HTTP/1.1 206 Partial Content';
        $headerLines = [
            'Last-Modified: ' . \gmdate(\DATE_RFC7231, (int) \filemtime($filename)),
            'Content-Disposition: attachment; filename="fo_o.b&quot;ar"',
            'Accept-Ranges: bytes',
            'Content-Length: ' . $length,
            'Content-Type: multipart/x-byteranges; boundary=' . $boundary,
            'Date: ' . \gmdate(\DATE_RFC7231),
        ];
        $this->response->setDownload($filename, filename: 'fo/o.b"ar');
        $message = $startLine . "\r\n"
            . \implode("\r\n", $headerLines) . "\r\n"
            . "\r\n"
            . $body;
        self::assertSame($message, (string) $this->response);
    }

    public function testUnknownStatus() : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unknown status code must have a default reason: 483');
        $this->response->setStatus(483);
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

    /**
     * @runInSeparateProcess
     */
    public function testContentTypeNotSetIfBodyIsEmpty() : void
    {
        \ob_start();
        $this->response->send();
        \ob_end_clean();
        self::assertNull($this->response->getHeader('content-type'));
        self::assertSame('', $this->response->getBody());
        self::assertSame('', \ini_get('default_mimetype'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testContentTypeAutoSetIfBodyIsNotEmpty() : void
    {
        $this->response->setBody('Foo');
        \ob_start();
        $this->response->send();
        \ob_end_clean();
        self::assertSame(
            'text/html; charset=UTF-8',
            $this->response->getHeader('content-type')
        );
        self::assertSame('Foo', $this->response->getBody());
        self::assertNotSame('', \ini_get('default_mimetype'));
    }

    /**
     * @dataProvider
     *
     * @return array<array<string>>
     */
    public function serverSoftwareProvider() : array
    {
        return [
            ['Apache/2.4.52'],
            ['lighttpd/1.4.63'],
            ['nginx/1.18.0'],
        ];
    }

    /**
     * @dataProvider serverSoftwareProvider
     *
     * @runInSeparateProcess
     */
    public function testContentTypeEmptyOnServer(string $software) : void
    {
        $_SERVER['SERVER_SOFTWARE'] = $software;
        $this->response->setBody('');
        \ob_start();
        $this->response->send();
        \ob_end_clean();
        self::assertNull($this->response->getHeader('content-type'));
        self::assertSame('', \ini_get('default_mimetype'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRemoveContentTypeOnDevelopmentServer() : void
    {
        self::assertSame('text/html', \ini_get('default_mimetype'));
        $_SERVER['SERVER_SOFTWARE'] = 'PHP 8.1.9 Development Server';
        $this->response->setBody('');
        \ob_start();
        $this->response->send();
        \ob_end_clean();
        self::assertNull($this->response->getHeader('content-type'));
        self::assertSame('', \ini_get('default_mimetype'));
        foreach (xdebug_get_headers() as $header) {
            $header = \strtolower($header);
            self::assertStringNotContainsString('content-type', $header);
        }
    }
}
