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

use PHPUnit\Framework\TestCase;

final class RequestProxyTest extends TestCase
{
    protected RequestProxyMock $proxyRequest;

    public function setUp() : void
    {
        $this->proxyRequest = new RequestProxyMock([
            'real-domain.tld:8080',
        ]);
    }

    public function testHost() : void
    {
        self::assertSame('real-domain.tld', $this->proxyRequest->getHost());
    }

    public function testAccept() : void
    {
        self::assertSame([], $this->proxyRequest->getAccepts());
    }

    public function testIsAjax() : void
    {
        self::assertFalse($this->proxyRequest->isAjax());
        self::assertFalse($this->proxyRequest->isAjax());
    }

    public function testIsSecure() : void
    {
        self::assertTrue($this->proxyRequest->isSecure());
        self::assertTrue($this->proxyRequest->isSecure());
    }

    public function testJson() : void
    {
        $this->proxyRequest->setBody('{"test":123}'); // @phpstan-ignore-line
        self::assertSame(123, $this->proxyRequest->getJson()->test); // @phpstan-ignore-line
    }

    public function testPort() : void
    {
        self::assertSame(8080, $this->proxyRequest->getPort());
    }

    public function testProxiedIp() : void
    {
        self::assertSame('192.168.1.2', $this->proxyRequest->getProxiedIp());
    }

    public function testReferer() : void
    {
        self::assertNull($this->proxyRequest->getReferer());
    }

    public function testURL() : void
    {
        self::assertSame(
            'https://real-domain.tld:8080/blog/posts?order_by=title&order=asc',
            (string) $this->proxyRequest->getUrl()
        );
        self::assertInstanceOf(\Framework\HTTP\URL::class, $this->proxyRequest->getUrl());
    }

    public function testId() : void
    {
        self::assertNull($this->proxyRequest->getId());
        self::assertNull($this->proxyRequest->getId());
    }
}
