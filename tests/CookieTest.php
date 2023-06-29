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
use PHPUnit\Framework\TestCase;

final class CookieTest extends TestCase
{
    protected Cookie $cookie;

    protected function setUp() : void
    {
        $this->cookie = new Cookie('foo', 'bar');
    }

    public function testDomain() : void
    {
        self::assertNull($this->cookie->getDomain());
        $this->cookie->setDomain('domain.tld');
        self::assertSame('domain.tld', $this->cookie->getDomain());
        $this->cookie->setDomain(null);
        self::assertNull($this->cookie->getDomain());
    }

    public function testExpires() : void
    {
        self::assertNull($this->cookie->getExpires());
        $this->cookie->setExpires('+5 seconds');
        self::assertInstanceOf(\DateTime::class, $this->cookie->getExpires());
        self::assertSame((string) (\time() + 5), $this->cookie->getExpires()->format('U'));
        self::assertFalse($this->cookie->isExpired());
        $this->cookie->setExpires($time = \time() + 30);
        self::assertInstanceOf(\DateTime::class, $this->cookie->getExpires());
        self::assertSame((string) $time, $this->cookie->getExpires()->format('U'));
        $this->cookie->setExpires(new \DateTime('-5 hours'));
        self::assertInstanceOf(\DateTime::class, $this->cookie->getExpires());
        self::assertSame((string) (\time() - 5 * 60 * 60), $this->cookie->getExpires()->format('U'));
        self::assertTrue($this->cookie->isExpired());
        $this->cookie->setExpires(null);
        self::assertNull($this->cookie->getExpires());
        $this->expectException(\Exception::class);
        $this->cookie->setExpires('foo');
    }

    public function testHttpOnly() : void
    {
        self::assertFalse($this->cookie->isHttpOnly());
        $this->cookie->setHttpOnly();
        self::assertTrue($this->cookie->isHttpOnly());
        $this->cookie->setHttpOnly(false);
        self::assertFalse($this->cookie->isHttpOnly());
    }

    public function testName() : void
    {
        self::assertSame('foo', $this->cookie->getName());
    }

    public function testPath() : void
    {
        self::assertNull($this->cookie->getPath());
        $this->cookie->setPath('/blog');
        self::assertSame('/blog', $this->cookie->getPath());
        $this->cookie->setPath(null);
        self::assertNull($this->cookie->getPath());
    }

    public function testSameSite() : void
    {
        self::assertNull($this->cookie->getSameSite());
        $this->cookie->setSameSite('sTrIcT');
        self::assertSame('Strict', $this->cookie->getSameSite());
        $this->cookie->setSameSite('lAx');
        self::assertSame('Lax', $this->cookie->getSameSite());
        $this->cookie->setSameSite('uNsEt');
        self::assertSame('Unset', $this->cookie->getSameSite());
        $this->cookie->setSameSite(null);
        self::assertNull($this->cookie->getSameSite());
        $this->expectException(\InvalidArgumentException::class);
        $this->cookie->setSameSite('foo');
    }

    public function testSecure() : void
    {
        self::assertFalse($this->cookie->isSecure());
        $this->cookie->setSecure();
        self::assertTrue($this->cookie->isSecure());
        $this->cookie->setSecure(false);
        self::assertFalse($this->cookie->isSecure());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSend() : void
    {
        self::assertTrue($this->cookie->send());
        self::assertSame(['Set-Cookie: foo=bar'], xdebug_get_headers());
        (new Cookie('foo', 'abc123'))->setSecure()->setHttpOnly()->send();
        (new Cookie('foo', 'abc123'))->setExpires('+5 seconds')->send();
        $xdebugCookieDateFormat = \PHP_VERSION_ID < 80200
            ? 'D, d-M-Y H:i:s'
            : 'D, d M Y H:i:s';
        self::assertSame([
            'Set-Cookie: foo=bar',
            'Set-Cookie: foo=abc123; secure; HttpOnly',
            'Set-Cookie: foo=abc123; expires='
            . \date('D, d-M-Y H:i:s', \time() + 5) . ' GMT; Max-Age=5',
        ], xdebug_get_headers());
        $this->cookie->setDomain('domain.tld')
            ->setPath('/blog')
            ->setSecure()
            ->setHttpOnly()
            ->setSameSite('strict')
            ->setValue('baz')
            ->setExpires('+30 seconds')
            ->send();
        $value = $this->cookie->toString();
        if (\PHP_VERSION_ID < 80200) {
            $time = \time() + 30;
            $value = \strtr($value, [
                \gmdate('D, d M Y H:i:s', $time) => \gmdate('D, d-M-Y H:i:s', $time),
            ]);
        }
        self::assertContains(
            'Set-Cookie: ' . $value,
            xdebug_get_headers()
        );
    }

    public function testString() : void
    {
        $time = \time() + 30;
        $expected = 'foo=baz; expires='
            . \date('D, d M Y H:i:s', $time)
            . ' GMT; Max-Age=30; path=/blog; domain=domain.tld; secure; HttpOnly; SameSite=Strict';
        $this->cookie->setDomain('domain.tld')
            ->setPath('/blog')
            ->setSecure()
            ->setHttpOnly()
            ->setSameSite('strict')
            ->setValue('baz')
            ->setExpires($time);
        self::assertSame($expected, $this->cookie->toString());
        self::assertSame($expected, (string) $this->cookie);
        self::assertInstanceOf(Cookie::class, $this->cookie);
    }

    public function testValue() : void
    {
        self::assertSame('bar', $this->cookie->getValue());
        $this->cookie->setValue(12345); // @phpstan-ignore-line
        self::assertSame('12345', $this->cookie->getValue());
    }

    public function testParse() : void
    {
        $cookie = Cookie::parse('session_id=35ab1d7a4955d926a3694ab5990c0eb1; expires=Thu, 11-Jul-2019 04:57:19 GMT; Max-Age=0; path=/admin; domain=localhost; secure; HttpOnly; SameSite=Strict');
        self::assertSame('session_id', $cookie->getName());
        self::assertSame('35ab1d7a4955d926a3694ab5990c0eb1', $cookie->getValue());
        self::assertSame('Thu, 11 Jul 2019 04:57:19 +0000', $cookie->getExpires()->format('r'));
        self::assertSame('/admin', $cookie->getPath());
        self::assertSame('localhost', $cookie->getDomain());
        self::assertTrue($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertSame('Strict', $cookie->getSameSite());
        $cookie = Cookie::parse('sid=foo;secure;;HttpOnly;');
        self::assertSame('sid', $cookie->getName());
        self::assertSame('foo', $cookie->getValue());
        self::assertTrue($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertNull(Cookie::parse('sid'));
        self::assertNull(Cookie::parse(';sid=foo'));
    }

    public function testCreate() : void
    {
        $cookies = Cookie::create('a=A; b=B;c=C;d;e = E ; =x; f  =  0 ');
        self::assertCount(5, $cookies);
        self::assertSame([
            'A',
            'B',
            'C',
            'E',
            '0',
        ], [
            $cookies['a']->getValue(),
            $cookies['b']->getValue(),
            $cookies['c']->getValue(),
            $cookies['e']->getValue(),
            $cookies['f']->getValue(),
        ]);
        self::assertCount(0, Cookie::create('foo'));
    }
}
