<?php
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\HTTP\Debug;

use Framework\HTTP\Cookie;
use Framework\HTTP\Debug\HTTPCollector;
use Framework\HTTP\Request;
use Framework\HTTP\Response;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
final class HTTPCollectorTest extends TestCase
{
    protected HTTPCollector $collector;

    /**
     * @param array<string,scalar> $server
     *
     * @return Response
     */
    protected function prepare(array $server = []) : Response
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.2';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo';
        $_SERVER['HTTP_HOST'] = 'domain.tld';
        foreach ($server as $key => $value) {
            $_SERVER[$key] = $value;
        }
        $request = new Request();
        $response = new Response($request);
        $this->collector = new HTTPCollector();
        $response->setDebugCollector($this->collector);
        return $response;
    }

    public function testEmpty() : void
    {
        $collector = new HTTPCollector();
        $contents = $collector->getContents();
        self::assertStringContainsString('Request instance has not been set', $contents);
        self::assertStringContainsString('Response instance has not been set', $contents);
    }

    public function testRequest() : void
    {
        $this->prepare();
        $contents = $this->collector->getContents();
        self::assertStringContainsString('192.168.0.2', $contents);
        self::assertStringContainsString('HTTP/1.1', $contents);
        self::assertStringContainsString('GET', $contents);
        self::assertStringContainsString('domain.tld', $contents);
        self::assertStringContainsString('Headers', $contents);
        self::assertStringContainsString('Host', $contents);
        self::assertStringNotContainsString('User-Agent', $contents);
    }

    public function testRequestUserAgent() : void
    {
        $this->prepare([
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:96.0) Gecko/20100101 Firefox/96.0',
        ]);
        $contents = $this->collector->getContents();
        self::assertStringContainsString('<h2>User-Agent</h2>', $contents);
        self::assertStringContainsString('Browser', $contents);
    }

    public function testRequestUserAgentIsRobot() : void
    {
        $this->prepare([
            'HTTP_USER_AGENT' => 'Googlebot-Image/1.0',
        ]);
        $contents = $this->collector->getContents();
        self::assertStringContainsString('<h2>User-Agent</h2>', $contents);
        self::assertStringContainsString('Robot', $contents);
    }

    public function testRequestUserAgentTypeNotDetected() : void
    {
        $this->prepare([
            'HTTP_USER_AGENT' => 'foo/1.2',
        ]);
        $contents = $this->collector->getContents();
        self::assertStringNotContainsString('<h2>User-Agent</h2>', $contents);
    }

    public function testRequestForm() : void
    {
        $_POST['foo']['bar'] = 'baz';
        $this->prepare([
            'REQUEST_METHOD' => 'POST',
            'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        ]);
        $contents = $this->collector->getContents();
        self::assertStringContainsString('<h2>Form</h2>', $contents);
        self::assertStringContainsString('foo[bar]', $contents);
    }

    public function testRequestUploadedFiles() : void
    {
        $_POST['foo']['bar'] = 'baz';
        $filename1 = \sys_get_temp_dir() . '/test1';
        $filename2 = \sys_get_temp_dir() . '/test2';
        \touch($filename1);
        \touch($filename2);
        $_FILES = [
            'file' => [
                'name' => [
                    0 => '656173.jpg',
                ],
                'full_path' => [
                    0 => '656173.jpg',
                ],
                'type' => [
                    0 => 'image/jpeg',
                ],
                'tmp_name' => [
                    0 => $filename1,
                ],
                'error' => [
                    0 => 0,
                ],
                'size' => [
                    0 => 269264,
                ],
            ],
            'bar' => [
                'name' => '32120040o.jpg',
                'full_path' => '32120040o.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $filename2,
                'error' => 0,
                'size' => 774338,
            ],
        ];
        $this->prepare([
            'REQUEST_METHOD' => 'POST',
            'HTTP_CONTENT_TYPE' => 'multipart/form-data; boundary=-',
        ]);
        $contents = $this->collector->getContents();
        self::assertStringContainsString('<h2>Form</h2>', $contents);
        self::assertStringContainsString('foo[bar]', $contents);
        self::assertStringContainsString('<h2>Uploaded Files</h2>', $contents);
        self::assertStringContainsString('file[0]', $contents);
        self::assertStringContainsString('32120040o.jpg', $contents);
        self::assertStringContainsString('bar', $contents);
    }

    public function testResponseCookies() : void
    {
        $this->prepare()
            ->setCookie(new Cookie('x-token', 'foo'))
            ->setCookie(new Cookie('session_id', 'abc'));
        $contents = $this->collector->getContents();
        self::assertStringContainsString('<h2>Cookies</h2>', $contents);
        self::assertStringContainsString('x-token', $contents);
        self::assertStringContainsString('session_id', $contents);
    }

    public function testResponseBody() : void
    {
        \ob_start();
        $this->prepare()->setBody('HTML Foo')->send();
        \ob_end_clean();
        $contents = $this->collector->getContents();
        self::assertStringContainsString('<h2>Body Contents</h2>', $contents);
        self::assertStringContainsString('HTML Foo', $contents);
    }

    public function testResponseBodyIsEmpty() : void
    {
        \ob_start();
        $this->prepare()->send();
        \ob_end_clean();
        $contents = $this->collector->getContents();
        self::assertStringContainsString('<h2>Body Contents</h2>', $contents);
        self::assertStringContainsString('Body is empty', $contents);
    }

    public function testResponseDownload() : void
    {
        \ob_start(static fn ($data) => '');
        $this->prepare()->setDownload(__FILE__)->send();
        \ob_end_clean();
        $contents = $this->collector->getContents();
        self::assertStringContainsString('<h2>Body Contents</h2>', $contents);
        self::assertStringContainsString('Body has downloadable content', $contents);
    }

    public function testGetActivities() : void
    {
        $response = $this->prepare();
        self::assertEmpty($this->collector->getActivities());
        \ob_start();
        $response->send();
        \ob_end_clean();
        self::assertSame([
            'collector',
            'class',
            'description',
            'start',
            'end',
        ], \array_keys($this->collector->getActivities()[0]));
    }
}
