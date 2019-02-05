<?php namespace Tests\HTTP;

use Framework\HTTP\URL;
use Framework\HTTP\Exceptions\URLException;
use PHPUnit\Framework\TestCase;

class URLTest extends TestCase
{
	/**
	 * @var \Framework\HTTP\URL;
	 */
	protected $url;

	public function setUp()
	{
		$this->url = new URL('http://user:pass@domain.tld:8080/foo/bar?a=1&b=2#id');
	}

	public function testURL()
	{
		$this->assertEquals([
			'scheme'   => 'http',
			'user'     => 'user',
			'pass'     => 'pass',
			'host'     => 'domain.tld',
			'port'     => 8080,
			'path'     => ['foo', 'bar'],
			'query'    => ['a' => '1', 'b' => '2'],
			'fragment' => 'id',
		], $this->url->getURL(true));

		$this->assertEquals('http://user:pass@domain.tld:8080/foo/bar?a=1&b=2#id',
			$this->url->getURL());

		$this->assertEquals('http://user:pass@domain.tld:8080/foo/bar?a=1&b=2#id',
			$this->url->__toString());
	}

	public function testInvalidURL()
	{
		$this->expectException(URLException::class);

		(new URL('//unknow'));
	}

	public function testScheme()
	{
		$this->assertEquals('http', $this->url->getScheme());

		$this->url->setScheme('https');

		$this->assertEquals('https', $this->url->getScheme());
	}

	public function testHost()
	{
		$this->assertEquals('domain.tld', $this->url->getHost());

		$this->url->setHost('do-main.com');

		$this->assertEquals('do-main.com', $this->url->getHost());

		$this->expectException(URLException::class);

		$this->url->setHost('in_valid.com');
	}

	public function testPort()
	{
		$this->assertEquals(8080, $this->url->getPort());

		$this->url->setPort(80);

		$this->assertEquals(80, $this->url->getPort());

		$this->expectException(URLException::class);

		$this->url->setPort(100000);
	}

	public function testPath()
	{
		$this->assertEquals('/foo/bar', $this->url->getPath());
		$this->assertEquals(['foo', 'bar'], $this->url->getPath(true));

		$this->url->setPath('a/b/c');

		$this->assertEquals('/a/b/c', $this->url->getPath());
		$this->assertEquals(['a', 'b', 'c'], $this->url->getPath(true));

		$this->url->setPath('/a/b/c');

		$this->assertEquals('/a/b/c', $this->url->getPath());
		$this->assertEquals(['a', 'b', 'c'], $this->url->getPath(true));

		$this->url->setPath('a/b/c/');

		$this->assertEquals('/a/b/c', $this->url->getPath());
		$this->assertEquals(['a', 'b', 'c'], $this->url->getPath(true));

		$this->url->setPath('/a/b/c/');

		$this->assertEquals('/a/b/c', $this->url->getPath());
		$this->assertEquals(['a', 'b', 'c'], $this->url->getPath(true));

		$this->url->setPath(['hello', 'bye']);

		$this->assertEquals('/hello/bye', $this->url->getPath());
		$this->assertEquals(['hello', 'bye'], $this->url->getPath(true));
	}

	public function testQuery()
	{
		$this->assertEquals('a=1&b=2', $this->url->getQuery());
		$this->assertEquals(['a' => '1', 'b' => '2'], $this->url->getQuery(true));

		$this->url->setQuery('?color=red&border=1');

		$this->assertEquals('color=red&border=1', $this->url->getQuery());
		$this->assertEquals(['color' => 'red', 'border' => '1'], $this->url->getQuery(true));

		$this->url->setQuery(['color' => 'red', 'border' => 1]);

		$this->assertEquals('color=red&border=1', $this->url->getQuery());
		$this->assertEquals(['color' => 'red', 'border' => 1], $this->url->getQuery(true));

		$this->url->addQuery('border', 2);
		$this->url->addQuery(['color' => 'blue']);
		$this->url->addQuery('a', 0);

		$this->assertEquals('color=blue&border=2&a=0', $this->url->getQuery());
		$this->assertEquals(['color' => 'blue', 'border' => 2, 'a' => 0],
			$this->url->getQuery(true));

		$this->url->removeQuery('a');

		$this->assertEquals('color=blue&border=2', $this->url->getQuery());
		$this->assertEquals(['color' => 'blue', 'border' => 2], $this->url->getQuery(true));
	}

	public function testQueryOnly()
	{
		$this->assertEquals('b=2', $this->url->getQuery(false, ['b']));
		$this->assertEquals(['b' => '2'], $this->url->getQuery(true, ['b']));

		$this->url->setQuery('?color=red&border=1');

		$this->assertEquals('border=1', $this->url->getQuery(false, ['border']));
		$this->assertEquals(['border' => '1'], $this->url->getQuery(true, ['border']));

		$this->url->setQuery(['color' => 'red', 'border' => 1]);

		$this->assertEquals('border=1', $this->url->getQuery(false, ['border']));
		$this->assertEquals(['border' => 1], $this->url->getQuery(true, ['border']));

		$this->url->setQuery('?color=red&border=1', ['border']);

		$this->assertEquals('border=1', $this->url->getQuery());
		$this->assertEquals(['border' => '1'], $this->url->getQuery(true));

		$this->url->setQuery(['color' => 'red', 'border' => 1], ['border']);

		$this->assertEquals('border=1', $this->url->getQuery());
		$this->assertEquals(['border' => 1], $this->url->getQuery(true));
	}
}
