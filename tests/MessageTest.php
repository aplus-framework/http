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

use Framework\HTTP\Cookie;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase
{
    protected MessageMock $message;

    protected function setUp() : void
    {
        $this->message = new MessageMock();
    }

    public function testProtocol() : void
    {
        self::assertSame('HTTP/1.1', $this->message->getProtocol());
        $this->message->setProtocol('HTTP/2');
        self::assertSame('HTTP/2', $this->message->getProtocol());
    }

    public function testHeader() : void
    {
        self::assertFalse($this->message->hasHeader('from'));
        self::assertNull($this->message->getHeader('from'));
        $this->message->setHeader('from', 'foo@localhost');
        self::assertTrue($this->message->hasHeader('from'));
        self::assertSame('foo@localhost', $this->message->getHeader('from'));
        self::assertSame(['from' => 'foo@localhost'], $this->message->getHeaders());
        $this->message->setHeader('from', 'bar@localhost');
        self::assertSame('bar@localhost', $this->message->getHeader('from'));
        $this->message->removeHeader('FROM');
        self::assertNull($this->message->getHeader('from'));
    }

    public function testHeaders() : void
    {
        self::assertSame([], $this->message->getHeaders());
        $this->message->setHeader('from', 'foo@localhost');
        self::assertSame(
            ['from' => 'foo@localhost'],
            $this->message->getHeaders()
        );
        $this->message->setHeaders([
            'content-type' => 'application/json',
            'allow' => '*',
        ]);
        self::assertSame(
            [
                'from' => 'foo@localhost',
                'content-type' => 'application/json',
                'allow' => '*',
            ],
            $this->message->getHeaders()
        );
        $this->message->removeHeaders();
        self::assertSame([], $this->message->getHeaders());
    }

    public function testHeaderNames() : void
    {
        self::assertSame('Host', $this->message::getHeaderName('host'));
        $this->message::setHeaderName('HOsT');
        self::assertSame('HOsT', $this->message::getHeaderName('host'));
    }

    public function testHeaderLine() : void
    {
        self::assertNull($this->message->getHeaderLine('content-type'));
        $this->message->setHeader('content-type', 'text/plain');
        self::assertSame(
            'Content-Type: text/plain',
            $this->message->getHeaderLine('content-type')
        );
    }

    public function testHeaderLines() : void
    {
        self::assertSame([], $this->message->getHeaders());
        $this->message->setHeader('from', 'foo@localhost');
        self::assertSame(
            ['From: foo@localhost'],
            $this->message->getHeaderLines()
        );
        $this->message->setHeaders([
            'content-type' => 'application/json',
            'ALLOW' => '*',
        ]);
        self::assertSame(
            [
                'From: foo@localhost',
                'Content-Type: application/json',
                'Allow: *',
            ],
            $this->message->getHeaderLines()
        );
        $this->message->removeHeaders();
        self::assertSame([], $this->message->getHeaders());
    }

    public function testBody() : void
    {
        self::assertSame('', $this->message->getBody());
        $this->message->setBody('hello');
        self::assertSame('hello', $this->message->getBody());
    }

    public function testCookie() : void
    {
        self::assertSame([], $this->message->getCookies());
        self::assertNull($this->message->getCookie('session'));
        self::assertFalse($this->message->hasCookie('session'));
        $this->message->setCookie(new Cookie('session', 'abc123'));
        self::assertTrue($this->message->hasCookie('session'));
        $this->message->setCookie(new Cookie('custom', 'foo'));
        self::assertSame('abc123', $this->message->getCookie('session')->getValue());
        self::assertSame(['session', 'custom'], \array_keys($this->message->getCookies()));
        $this->message->removeCookies([
            'session',
            'custom',
        ]);
        self::assertSame([], $this->message->getCookies());
        $this->message->setCookies([new Cookie('other', 'foo')]);
        self::assertSame(['other'], \array_keys($this->message->getCookies()));
    }

    public function testParseContentType() : void
    {
        self::assertNull($this->message->parseContentType());
        $this->message->setHeader('Content-Type', 'text/html; charset=UTF-8');
        self::assertSame('text/html', $this->message->parseContentType());
    }
}
