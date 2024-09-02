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

use Framework\HTTP\URL;
use PHPUnit\Framework\TestCase;

final class URLTest extends TestCase
{
    protected URL $url;

    public function setUp() : void
    {
        $this->url = new URL('http://user:pass@domain.tld:8080/foo/bar?a=1&b=2#id');
    }

    public function testSameUrl() : void
    {
        $url = 'http://domain.tld';
        self::assertNotSame($url, (new URL($url))->__toString());
        $url = 'http://domain.tld/';
        self::assertSame($url, (new URL($url))->__toString());
        $url = 'http://domain.tld/foo';
        self::assertSame($url, (new URL($url))->__toString());
        $url = 'http://domain.tld/foo/';
        self::assertSame($url, (new URL($url))->__toString());
        $url = 'http://domain.tld/foo/bar';
        self::assertSame($url, (new URL($url))->__toString());
        $url = 'http://domain.tld/foo/bar/';
        self::assertSame($url, (new URL($url))->__toString());
        $url = 'http://user:pass@domain.tld:8080/foo/bar?a=1&b=2#id';
        self::assertSame($url, (new URL($url))->__toString());
        $url = 'http://user:pass@domain.tld:8080/foo/bar/?a=1&b=2#id';
        self::assertSame($url, (new URL($url))->__toString());
    }

    public function testPathEndsWithBar() : void
    {
        $url = 'http://domain.tld';
        self::assertSame(
            $url . '/',
            (new URL($url))->__toString()
        );
    }

    public function testBaseUrl() : void
    {
        self::assertSame('http://domain.tld:8080/', $this->url->getBaseUrl());
        self::assertSame('http://domain.tld:8080', $this->url->getBaseURL(''));
        self::assertSame('http://domain.tld:8080/foo/bar', $this->url->getBaseUrl('foo/bar'));
        self::assertSame('http://domain.tld:8080/foo/bar/', $this->url->getBaseUrl('foo/bar/'));
        $this->url->setScheme('https');
        $this->url->setPort(443);
        self::assertSame('https://domain.tld/', $this->url->getBaseUrl());
        self::assertSame('https://domain.tld', $this->url->getBaseUrl(''));
        self::assertSame('https://domain.tld/foo/bar', $this->url->getBaseUrl('foo/bar'));
        self::assertSame('https://domain.tld/foo/bar/', $this->url->getBaseUrl('/foo/bar/'));
    }

    public function testHost() : void
    {
        self::assertSame('domain.tld:8080', $this->url->getHost());
        $this->url->setHostname('do-main.com');
        self::assertSame('do-main.com:8080', $this->url->getHost());
        $this->expectException(\InvalidArgumentException::class);
        $this->url->setHostname('in_valid.com');
    }

    public function testHostname() : void
    {
        self::assertSame('domain.tld', $this->url->getHostname());
        $this->url->setHostname('do-main.com');
        self::assertSame('do-main.com', $this->url->getHostname());
        $this->expectException(\InvalidArgumentException::class);
        $this->url->setHostname('in_valid.com');
    }

    public function testInvalidURL() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL: //unknown');
        new URL('//unknown');
    }

    public function testInvalidUrlNoScheme() : void
    {
        $url = new URL('https://unknown.com');
        self::assertSame('https', $url->getScheme());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL: //unknown.com');
        new URL('//unknown.com');
    }

    public function testInvalidUrlNoSchemeAndNoBars() : void
    {
        $url = new URL('https://unknown.com');
        self::assertSame('https', $url->getScheme());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL: unknown.com');
        new URL('unknown.com');
    }

    public function testInvalidUrlNoHost() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL: /foo/bar');
        new URL('/foo/bar');
    }

    public function testOrigin() : void
    {
        self::assertSame('http://domain.tld:8080', $this->url->getOrigin());
        $this->url->setScheme('https');
        $this->url->setPort(443);
        self::assertSame('https://domain.tld', $this->url->getOrigin());
    }

    public function testParsedUrl() : void
    {
        self::assertSame([
            'scheme' => 'http',
            'user' => 'user',
            'pass' => 'pass',
            'hostname' => 'domain.tld',
            'port' => 8080,
            'path' => ['foo', 'bar'],
            'query' => ['a' => '1', 'b' => '2'],
            'fragment' => 'id',
        ], $this->url->getParsedUrl());
    }

    public function testPath() : void
    {
        self::assertSame('/foo/bar', $this->url->getPath());
        $this->url->setPath('a/b/c');
        self::assertSame('/a/b/c', $this->url->getPath());
        $this->url->setPath('/a/b/c');
        self::assertSame('/a/b/c', $this->url->getPath());
        $this->url->setPath('a/b/c/');
        self::assertSame('/a/b/c/', $this->url->getPath());
        $this->url->setPath('/a/b/c/');
        self::assertSame('/a/b/c/', $this->url->getPath());
        $this->url->setPathSegments([]);
        self::assertSame('/', $this->url->getPath());
        $this->url->setPathSegments(['hello', 'bye']);
        self::assertSame('/hello/bye', $this->url->getPath());
        $this->url->setPathSegments(['hello', 'bye', '']);
        self::assertSame('/hello/bye/', $this->url->getPath());
    }

    public function testPathSegments() : void
    {
        self::assertSame(['foo', 'bar'], $this->url->getPathSegments());
        $this->url->setPath('a/b/c');
        self::assertSame(['a', 'b', 'c'], $this->url->getPathSegments());
        $this->url->setPath('/a/b/c');
        self::assertSame(['a', 'b', 'c'], $this->url->getPathSegments());
        $this->url->setPath('a/b/c/');
        self::assertSame(['a', 'b', 'c', ''], $this->url->getPathSegments());
        $this->url->setPath('/a/b/c/');
        self::assertSame(['a', 'b', 'c', ''], $this->url->getPathSegments());
        $this->url->setPathSegments(['hello', 'bye']);
        self::assertSame(['hello', 'bye'], $this->url->getPathSegments());
        $this->url->setPathSegments(['hello', 'bye', '']);
        self::assertSame(['hello', 'bye', ''], $this->url->getPathSegments());
        self::assertSame('hello', $this->url->getPathSegment(0));
        self::assertSame('bye', $this->url->getPathSegment(1));
        self::assertSame('', $this->url->getPathSegment(2));
        self::assertNull($this->url->getPathSegment(3));
    }

    public function testPort() : void
    {
        self::assertSame(8080, $this->url->getPort());
        $this->url->setPort(80);
        self::assertSame(80, $this->url->getPort());
        $this->expectException(\InvalidArgumentException::class);
        $this->url->setPort(100000);
    }

    public function testQuery() : void
    {
        self::assertSame('a=1&b=2', $this->url->getQuery());
        self::assertSame(['a' => '1', 'b' => '2'], $this->url->getQueryData());
        $this->url->setQuery('?color=red&border=1');
        self::assertSame('color=red&border=1', $this->url->getQuery());
        self::assertSame(['color' => 'red', 'border' => '1'], $this->url->getQueryData());
        $this->url->setQueryData(['color' => 'red', 'border' => 1]);
        self::assertSame('color=red&border=1', $this->url->getQuery());
        self::assertSame(['color' => 'red', 'border' => 1], $this->url->getQueryData());
        $this->url->addQuery('border', 2);
        $this->url->addQueries(['color' => 'blue']);
        $this->url->addQuery('a', 0);
        self::assertSame('color=blue&border=2&a=0', $this->url->getQuery());
        self::assertSame(
            ['color' => 'blue', 'border' => 2, 'a' => 0],
            $this->url->getQueryData()
        );
        $this->url->removeQueryData('a');
        self::assertSame('color=blue&border=2', $this->url->getQuery());
        self::assertSame(['color' => 'blue', 'border' => 2], $this->url->getQueryData());
    }

    public function testQueryOnly() : void
    {
        self::assertSame('b=2', $this->url->getQuery(['b']));
        self::assertSame(['b' => '2'], $this->url->getQueryData(['b']));
        $this->url->setQuery('?color=red&border=1');
        self::assertSame('border=1', $this->url->getQuery(['border']));
        self::assertSame(['border' => '1'], $this->url->getQueryData(['border']));
        $this->url->setQueryData(['color' => 'red', 'border' => 1]);
        self::assertSame('border=1', $this->url->getQuery(['border']));
        self::assertSame(['border' => 1], $this->url->getQueryData(['border']));
        $this->url->setQuery('?color=red&border=1', ['border']);
        self::assertSame('border=1', $this->url->getQuery());
        self::assertSame(['border' => '1'], $this->url->getQueryData());
        $this->url->setQueryData(['color' => 'red', 'border' => 1], ['border']);
        self::assertSame('border=1', $this->url->getQuery());
        self::assertSame(['border' => 1], $this->url->getQueryData());
    }

    public function testScheme() : void
    {
        self::assertSame('http', $this->url->getScheme());
        $this->url->setScheme('https');
        self::assertSame('https', $this->url->getScheme());
    }

    public function testToString() : void
    {
        self::assertSame(
            'http://user:pass@domain.tld:8080/foo/bar?a=1&b=2#id',
            $this->url->toString()
        );
        self::assertSame(
            'http://user:pass@domain.tld:8080/foo/bar?a=1&b=2#id',
            $this->url->__toString()
        );
    }

    public function testJsonSerializable() : void
    {
        self::assertSame(
            '"http:\/\/user:pass@domain.tld:8080\/foo\/bar?a=1&b=2#id"',
            \json_encode($this->url)
        );
    }
}
