<?php namespace Tests\HTTP;

use Framework\HTTP\CSRF;
use PHPUnit\Framework\TestCase;

class CSRFTest extends TestCase
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

	public function testSessionDisabled()
	{
		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage('Session must be active to use CSRF class');
		(new CSRF(new RequestMock()));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testMakeToken()
	{
		$this->prepare();
		$this->assertEquals(64, \strlen($this->csrf->getToken()));
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testTokenName()
	{
		$this->prepare();
		$this->assertEquals('csrf_token', $this->csrf->getTokenName());
		$this->assertInstanceOf(CSRF::class, $this->csrf->setTokenName('custom'));
		$this->assertEquals('custom', $this->csrf->getTokenName());
		$this->assertInstanceOf(CSRF::class, $this->csrf->setTokenName("cus'tom"));
		$this->assertEquals('cus&apos;tom', $this->csrf->getTokenName());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testInput()
	{
		$this->prepare();
		$this->assertStringStartsWith(
			'<input type="hidden" name="csrf_token" value="',
			$this->csrf->input()
		);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testVerifyAllowedMethod()
	{
		$this->prepare();
		$this->request->setMethod('get');
		$this->assertTrue($this->csrf->verify());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testUserTokenEmpty()
	{
		$this->prepare();
		$this->request->input[\INPUT_POST] = [];
		$this->assertFalse($this->csrf->verify());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testVerifySuccess()
	{
		$this->prepare();
		$_SESSION['$']['csrf_token'] = 'foo';
		$this->assertTrue($this->csrf->verify());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testVerifyFail()
	{
		$this->prepare();
		$_SESSION['$']['csrf_token'] = 'bar';
		$this->assertFalse($this->csrf->verify());
	}
}
