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

    public function testTokenBytesLengthInConstructor() : void
    {
        \session_start();
        $request = new RequestMock();
        $request->setMethod('POST');
        $anti = new AntiCSRF($request, 32);
        self::assertSame(32, $anti->getTokenBytesLength());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'AntiCSRF token bytes length must be greater than 2, 1 given'
        );
        new AntiCSRF($request, 1);
    }

    public function testTokenBytesLength() : void
    {
        $this->prepare();
        self::assertSame(8, $this->anti->getTokenBytesLength());
        $this->anti->setTokenBytesLength(3);
        self::assertSame(3, $this->anti->getTokenBytesLength());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'AntiCSRF token bytes length must be greater than 2, 2 given'
        );
        $this->anti->setTokenBytesLength(2);
    }

    /**
     * @dataProvider tokenFunctionProvider
     *
     * @param string $function
     * @param int $length
     *
     * @return void
     */
    public function testGenerateTokenFunctionInConstructor(string $function, int $length) : void
    {
        \session_start();
        $request = new RequestMock();
        $request->setMethod('POST');
        $anti = new AntiCSRF($request, generateTokenFunction: $function);
        self::assertSame($function, $anti->getGenerateTokenFunction());
        self::assertSame($length, \strlen($anti->generateToken()));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid generate token function name: foo'
        );
        new AntiCSRF($request, generateTokenFunction: 'foo');
    }

    /**
     * @dataProvider tokenFunctionProvider
     *
     * @param string $function
     * @param int $length
     *
     * @return void
     */
    public function testGenerateToken(string $function, int $length) : void
    {
        $this->prepare();
        $this->anti->setGenerateTokenFunction($function);
        self::assertSame($function, $this->anti->getGenerateTokenFunction());
        self::assertSame($length, \strlen($this->anti->generateToken()));
    }

    public function testInvalidGenerateTokenFunction() : void
    {
        $this->prepare();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid generate token function name: foo'
        );
        $this->anti->setGenerateTokenFunction('foo');
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

    /**
     * @return array<array<scalar>>
     */
    public static function tokenFunctionProvider() : array
    {
        return [
            ['base64_encode', 12],
            ['bin2hex', 16],
            ['md5', 32],
        ];
    }
}
