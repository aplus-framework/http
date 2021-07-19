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

use Framework\HTTP\Response;
use PHPUnit\Framework\TestCase;

final class ResponseDownloadTest extends TestCase
{
    protected Response $response;
    protected RequestMock $request;

    protected function setUp() : void
    {
        $this->request = new RequestMock([
            'domain.tld',
        ]);
        $this->request->setHeader('Range', 'bytes=0-499');
        $this->response = new class($this->request) extends Response {
            public string $body = '';
        };
    }

    public function testInvalidFilepath() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->response->setDownload(__DIR__ . '/unknown');
    }

    public function testInvalidRequestHeader() : void
    {
        $this->request->setHeader('Range', 'xx');
        $this->response->setDownload(__FILE__);
        self::assertSame(
            Response::CODE_RANGE_NOT_SATISFIABLE,
            $this->response->getStatusCode()
        );
        self::assertStringStartsWith('*/', $this->response->getHeader('Content-Range'));
    }

    public function testSingleByteRanges() : void
    {
        $this->response->setDownload(__FILE__);
        self::assertSame(Response::CODE_PARTIAL_CONTENT, $this->response->getStatusCode());
        self::assertSame('text/x-php', $this->response->getHeader('Content-Type'));
        self::assertSame('500', $this->response->getHeader('Content-Length'));
        self::assertStringStartsWith('bytes 0-499/', $this->response->getHeader('Content-Range'));
    }

    public function testMultiByteRanges() : void
    {
        $this->request->setHeader('Range', 'bytes=0-499,500-549');
        $this->response->setDownload(__FILE__);
        self::assertSame(Response::CODE_PARTIAL_CONTENT, $this->response->getStatusCode());
        self::assertStringStartsWith(
            'multipart/x-byteranges; boundary=',
            $this->response->getHeader('Content-Type')
        );
        self::assertSame('822', $this->response->getHeader('Content-Length'));
    }

    public function testInvalidHeaderValue() : void
    {
        $this->request->setHeader('Range', 'x');
        $this->response->setDownload(__FILE__);
        self::assertSame(
            Response::CODE_RANGE_NOT_SATISFIABLE,
            $this->response->getStatusCode()
        );
    }

    public function testInvalidRange() : void
    {
        $this->request->setHeader('Range', 'bytes=');
        $this->response->setDownload(__FILE__);
        self::assertSame(Response::CODE_RANGE_NOT_SATISFIABLE, $this->response->getStatusCode());
        $this->request->setHeader('Range', 'bytes=-');
        $this->response->setDownload(__FILE__);
        self::assertSame(Response::CODE_RANGE_NOT_SATISFIABLE, $this->response->getStatusCode());
        $this->request->setHeader('Range', 'bytes=a-');
        $this->response->setDownload(__FILE__);
        self::assertSame(Response::CODE_RANGE_NOT_SATISFIABLE, $this->response->getStatusCode());
        $this->request->setHeader('Range', 'bytes=0-y');
        $this->response->setDownload(__FILE__);
        self::assertSame(Response::CODE_RANGE_NOT_SATISFIABLE, $this->response->getStatusCode());
        $this->request->setHeader('Range', 'bytes=0-10000');
        $this->response->setDownload(__FILE__);
        self::assertSame(Response::CODE_RANGE_NOT_SATISFIABLE, $this->response->getStatusCode());
    }

    public function testRange() : void
    {
        $this->request->setHeader('Range', 'bytes=0-10');
        $this->response->setDownload(__FILE__);
        self::assertSame(Response::CODE_PARTIAL_CONTENT, $this->response->getStatusCode());
        $this->request->setHeader('Range', 'bytes=0-10,11-19,25-,-98');
        $this->response->setDownload(__FILE__);
        self::assertSame(Response::CODE_PARTIAL_CONTENT, $this->response->getStatusCode());
    }

    public function testHasDownload() : void
    {
        self::assertFalse($this->response->hasDownload());
        $this->response->setDownload(__FILE__);
        self::assertTrue($this->response->hasDownload());
    }

    /**
     * This method should not be necessary, but it allows call a $function
     * enclosed by ob_start and ob_end_clean.
     *
     * Appending the $buffer to the Response body and returning an empty string
     * in ob_start do not send the output buffer, sent by ob_flush(), internally
     * in the ResponseDownload Trait, to the PHPUnit buffer. And the Response
     * body can be used for asserts.
     */
    protected function avoidPHPUnitOutputBuffer(\Closure $function) : void
    {
        \ob_start(function (string $buffer) : string {
            $this->response->body .= $buffer; // @phpstan-ignore-line
            return '';
        });
        $function();
        \ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendSinglePart() : void
    {
        $this->request->setHeader('Range', 'bytes=0-9');
        $this->response->setDownload(__FILE__);
        $this->avoidPHPUnitOutputBuffer(function () : void {
            $this->response->send();
        });
        self::assertTrue($this->response->isSent());
        self::assertSame(
            'bytes 0-9/' . \filesize(__FILE__),
            $this->response->getHeader('Content-Range')
        );
        self::assertSame('10', $this->response->getHeader('Content-Length'));
        self::assertSame(
            \substr(\file_get_contents(__FILE__), 0, 10), // @phpstan-ignore-line
            $this->response->getBody()
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendMultiPart() : void
    {
        $this->request->setHeader('Range', 'bytes=0-9,10-19');
        $this->response->setDownload(__FILE__);
        $this->avoidPHPUnitOutputBuffer(function () : void {
            $this->response->send();
        });
        self::assertTrue($this->response->isSent());
        self::assertStringStartsWith(
            'multipart/x-byteranges; boundary=',
            $this->response->getHeader('Content-Type')
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testReadFile() : void
    {
        $this->response->setDownload(__FILE__, false, false);
        $this->avoidPHPUnitOutputBuffer(function () : void {
            $this->response->send();
        });
        self::assertTrue($this->response->isSent());
    }
}
