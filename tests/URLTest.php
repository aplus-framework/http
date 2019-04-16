<?php namespace Tests\HTTP;

use Framework\HTTP\URL;
use PHPUnit\Framework\TestCase;

class URLTest extends TestCase
{
	/**
	 * @var URL;
	 */
	protected $url;

	public function setUp()
	{
		$this->url = new URL('http://user:pass@domain.tld:8080/foo/bar?a=1&b=2#id');
	}

	public function testBaseURL()
	{
		$this->assertEquals('http://domain.tld:8080/', $this->url->getBaseURL());
		$this->assertEquals('http://domain.tld:8080', $this->url->getBaseURL(''));
		$this->assertEquals('http://domain.tld:8080/foo/bar', $this->url->getBaseURL('foo/bar'));
		$this->url->setScheme('https');
		$this->url->setPort(443);
		$this->assertEquals('https://domain.tld/', $this->url->getBaseURL());
		$this->assertEquals('https://domain.tld', $this->url->getBaseURL(''));
		$this->assertEquals('https://domain.tld/foo/bar', $this->url->getBaseURL('foo/bar'));
	}

	public function testHost()
	{
		$this->assertEquals('domain.tld:8080', $this->url->getHost());
		$this->url->setHostname('do-main.com');
		$this->assertEquals('do-main.com:8080', $this->url->getHost());
		$this->expectException(\InvalidArgumentException::class);
		$this->url->setHostname('in_valid.com');
	}

	public function testHostname()
	{
		$this->assertEquals('domain.tld', $this->url->getHostname());
		$this->url->setHostname('do-main.com');
		$this->assertEquals('do-main.com', $this->url->getHostname());
		$this->expectException(\InvalidArgumentException::class);
		$this->url->setHostname('in_valid.com');
	}

	public function testInvalidURL()
	{
		$this->expectException(\InvalidArgumentException::class);
		new URL('//unknown');
	}

	public function testOrigin()
	{
		$this->assertEquals('http://domain.tld:8080', $this->url->getOrigin());
		$this->url->setScheme('https');
		$this->url->setPort(443);
		$this->assertEquals('https://domain.tld', $this->url->getOrigin());
	}

	public function testParsedURL()
	{
		$this->assertEquals([
			'scheme' => 'http',
			'user' => 'user',
			'pass' => 'pass',
			'hostname' => 'domain.tld',
			'port' => 8080,
			'path' => ['foo', 'bar'],
			'query' => ['a' => '1', 'b' => '2'],
			'fragment' => 'id',
		], $this->url->getParsedURL());
	}

	public function testPath()
	{
		$this->assertEquals('/foo/bar', $this->url->getPath());
		$this->url->setPath('a/b/c');
		$this->assertEquals('/a/b/c', $this->url->getPath());
		$this->url->setPath('/a/b/c');
		$this->assertEquals('/a/b/c', $this->url->getPath());
		$this->url->setPath('a/b/c/');
		$this->assertEquals('/a/b/c', $this->url->getPath());
		$this->url->setPath('/a/b/c/');
		$this->assertEquals('/a/b/c', $this->url->getPath());
		$this->url->setPathSegments(['hello', 'bye']);
		$this->assertEquals('/hello/bye', $this->url->getPath());
	}

	public function testPathSegments()
	{
		$this->assertEquals(['foo', 'bar'], $this->url->getPathSegments());
		$this->url->setPath('a/b/c');
		$this->assertEquals(['a', 'b', 'c'], $this->url->getPathSegments());
		$this->url->setPath('/a/b/c');
		$this->assertEquals(['a', 'b', 'c'], $this->url->getPathSegments());
		$this->url->setPath('a/b/c/');
		$this->assertEquals(['a', 'b', 'c'], $this->url->getPathSegments());
		$this->url->setPath('/a/b/c/');
		$this->assertEquals(['a', 'b', 'c'], $this->url->getPathSegments());
		$this->url->setPathSegments(['hello', 'bye']);
		$this->assertEquals(['hello', 'bye'], $this->url->getPathSegments());
	}

	public function testPort()
	{
		$this->assertEquals(8080, $this->url->getPort());
		$this->url->setPort(80);
		$this->assertEquals(80, $this->url->getPort());
		$this->expectException(\InvalidArgumentException::class);
		$this->url->setPort(100000);
	}

	public function testQuery()
	{
		$this->assertEquals('a=1&b=2', $this->url->getQuery());
		$this->assertEquals(['a' => '1', 'b' => '2'], $this->url->getQueryData());
		$this->url->setQuery('?color=red&border=1');
		$this->assertEquals('color=red&border=1', $this->url->getQuery());
		$this->assertEquals(['color' => 'red', 'border' => '1'], $this->url->getQueryData());
		$this->url->setQueryData(['color' => 'red', 'border' => 1]);
		$this->assertEquals('color=red&border=1', $this->url->getQuery());
		$this->assertEquals(['color' => 'red', 'border' => 1], $this->url->getQueryData());
		$this->url->addQuery('border', 2);
		$this->url->addQueries(['color' => 'blue']);
		$this->url->addQuery('a', 0);
		$this->assertEquals('color=blue&border=2&a=0', $this->url->getQuery());
		$this->assertEquals(
			['color' => 'blue', 'border' => 2, 'a' => 0],
			$this->url->getQueryData()
		);
		$this->url->removeQueryData('a');
		$this->assertEquals('color=blue&border=2', $this->url->getQuery());
		$this->assertEquals(['color' => 'blue', 'border' => 2], $this->url->getQueryData());
	}

	public function testQueryOnly()
	{
		$this->assertEquals('b=2', $this->url->getQuery(['b']));
		$this->assertEquals(['b' => '2'], $this->url->getQueryData(['b']));
		$this->url->setQuery('?color=red&border=1');
		$this->assertEquals('border=1', $this->url->getQuery(['border']));
		$this->assertEquals(['border' => '1'], $this->url->getQueryData(['border']));
		$this->url->setQueryData(['color' => 'red', 'border' => 1]);
		$this->assertEquals('border=1', $this->url->getQuery(['border']));
		$this->assertEquals(['border' => 1], $this->url->getQueryData(['border']));
		$this->url->setQuery('?color=red&border=1', ['border']);
		$this->assertEquals('border=1', $this->url->getQuery());
		$this->assertEquals(['border' => '1'], $this->url->getQueryData());
		$this->url->setQueryData(['color' => 'red', 'border' => 1], ['border']);
		$this->assertEquals('border=1', $this->url->getQuery());
		$this->assertEquals(['border' => 1], $this->url->getQueryData());
	}

	public function testScheme()
	{
		$this->assertEquals('http', $this->url->getScheme());
		$this->url->setScheme('https');
		$this->assertEquals('https', $this->url->getScheme());
	}

	public function testURL()
	{
		$this->assertEquals(
			'http://user:pass@domain.tld:8080/foo/bar?a=1&b=2#id',
			$this->url->getURL()
		);
		$this->assertEquals(
			'http://user:pass@domain.tld:8080/foo/bar?a=1&b=2#id',
			$this->url->__toString()
		);
	}
}
