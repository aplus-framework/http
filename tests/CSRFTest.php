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

use Framework\HTTP\CSRF;
use PHPUnit\Framework\TestCase;

/**
 * Class CSRFTest.
 *
 * @runTestsInSeparateProcesses
 */
final class CSRFTest extends TestCase
{
	protected CSRF $csrf;
	protected RequestMock $request;

	protected function prepare() : void
	{
		\session_start();
		$this->request = new RequestMock();
		$this->request->setMethod('POST');
		$this->csrf = new CSRF($this->request);
	}

	public function testSessionDisabled() : void
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Session must be active to use CSRF class');
		(new CSRF(new RequestMock()));
	}

	public function testMakeToken() : void
	{
		$this->prepare();
		self::assertSame(12, \strlen($this->csrf->getToken()));
	}

	public function testTokenName() : void
	{
		$this->prepare();
		self::assertSame('csrf_token', $this->csrf->getTokenName());
		self::assertInstanceOf(CSRF::class, $this->csrf->setTokenName('custom'));
		self::assertSame('custom', $this->csrf->getTokenName());
		self::assertInstanceOf(CSRF::class, $this->csrf->setTokenName("cus'tom"));
		self::assertSame('cus&apos;tom', $this->csrf->getTokenName());
	}

	public function testInput() : void
	{
		$this->prepare();
		self::assertStringStartsWith(
			'<input type="hidden" name="csrf_token" value="',
			$this->csrf->input()
		);
	}

	public function testVerifyAllowedMethod() : void
	{
		$this->prepare();
		$this->request->setMethod('get');
		self::assertTrue($this->csrf->verify());
	}

	public function testUserTokenEmpty() : void
	{
		$this->prepare();
		$_POST = [];
		self::assertFalse($this->csrf->verify());
	}

	public function testVerifySuccess() : void
	{
		$this->prepare();
		$_SESSION['$']['csrf_token'] = 'foo';
		self::assertTrue($this->csrf->verify());
	}

	public function testVerifyFail() : void
	{
		$this->prepare();
		$_SESSION['$']['csrf_token'] = 'bar';
		self::assertFalse($this->csrf->verify());
	}

	public function testEnableAndDisable() : void
	{
		$this->prepare();
		$this->csrf->enable();
		self::assertStringStartsWith(
			'<input type="hidden" name="csrf_token" value="',
			$this->csrf->input()
		);
		$_SESSION['$']['csrf_token'] = 'foo';
		self::assertTrue($this->csrf->verify());
		$this->csrf->disable();
		self::assertSame('', $this->csrf->input());
		$_SESSION['$']['csrf_token'] = 'bar';
		self::assertTrue($this->csrf->verify());
	}
}
