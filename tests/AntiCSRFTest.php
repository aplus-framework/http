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

use Framework\HTTP\AntiCSRF;
use PHPUnit\Framework\TestCase;

/**
 * Class AntiCSRFTest.
 *
 * @runTestsInSeparateProcesses
 */
final class AntiCSRFTest extends TestCase
{
    protected AntiCSRF $anti;
    protected RequestMock $request;

    protected function prepare() : void
    {
        \session_start();
        $this->request = new RequestMock();
        $this->request->setMethod('POST');
        $this->anti = new AntiCSRF($this->request);
    }

    public function testSessionDisabled() : void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Session must be active to use AntiCSRF class');
        (new AntiCSRF(new RequestMock()));
    }

    public function testMakeToken() : void
    {
        $this->prepare();
        self::assertSame(12, \strlen($this->anti->getToken()));
    }

    public function testTokenName() : void
    {
        $this->prepare();
        self::assertSame('csrf_token', $this->anti->getTokenName());
        self::assertInstanceOf(AntiCSRF::class, $this->anti->setTokenName('custom'));
        self::assertSame('custom', $this->anti->getTokenName());
        self::assertInstanceOf(AntiCSRF::class, $this->anti->setTokenName("cus'tom"));
        self::assertSame('cus&apos;tom', $this->anti->getTokenName());
    }

    public function testInput() : void
    {
        $this->prepare();
        self::assertStringStartsWith(
            '<input type="hidden" name="csrf_token" value="',
            $this->anti->input()
        );
    }

    public function testVerifyAllowedMethod() : void
    {
        $this->prepare();
        $this->request->setMethod('get');
        self::assertTrue($this->anti->verify());
    }

    public function testUserTokenEmpty() : void
    {
        $this->prepare();
        $_POST = [];
        self::assertFalse($this->anti->verify());
    }

    public function testUserTokenIsNotString() : void
    {
        $this->prepare();
        $_POST = [
            'csrf_token' => [
                'foo' => 'bar',
            ],
        ];
        self::assertFalse($this->anti->verify());
    }

    public function testVerifySuccess() : void
    {
        $this->prepare();
        $_SESSION['$']['csrf_token'] = 'foo';
        self::assertTrue($this->anti->verify());
    }

    public function testVerifyFail() : void
    {
        $this->prepare();
        $_SESSION['$']['csrf_token'] = 'bar';
        self::assertFalse($this->anti->verify());
    }

    public function testIsSafeMethod() : void
    {
        $this->prepare();
        self::assertFalse($this->anti->isSafeMethod());
        $this->request->setMethod('GET');
        self::assertTrue($this->anti->isSafeMethod());
        $this->request->setMethod('HEAD');
        self::assertTrue($this->anti->isSafeMethod());
        $this->request->setMethod('OPTIONS');
        self::assertTrue($this->anti->isSafeMethod());
        $this->request->setMethod('PUT');
        self::assertFalse($this->anti->isSafeMethod());
    }

    public function testEnableAndDisable() : void
    {
        $this->prepare();
        $this->anti->enable();
        self::assertStringStartsWith(
            '<input type="hidden" name="csrf_token" value="',
            $this->anti->input()
        );
        $_SESSION['$']['csrf_token'] = 'foo';
        self::assertTrue($this->anti->verify());
        $this->anti->disable();
        self::assertSame('', $this->anti->input());
        $_SESSION['$']['csrf_token'] = 'bar';
        self::assertTrue($this->anti->verify());
    }
}
