<?php namespace Tests\HTTP;

use Framework\HTTP\UploadedFile;
use PHPUnit\Framework\TestCase;

class UploadedFileTest extends TestCase
{
	/**
	 * @var array
	 */
	protected $file = [
		'name' => 'logo.jpg',
		'type' => 'foo/bar',
		'size' => 19878,
		'tmp_name' => __DIR__ . '/files/logo.png',
		'error' => 0,
	];
	/**
	 * @var UploadedFile;
	 */
	protected $uploadedFile;

	protected function setUp()
	{
		$this->uploadedFile = new UploadedFile($this->file);
	}

	public function testGetClientExtension()
	{
		$this->assertEquals('jpg', $this->uploadedFile->getClientExtension());
	}

	public function testGetExtension()
	{
		$this->assertEquals('png', $this->uploadedFile->getExtension());
		$this->assertEquals('png', $this->uploadedFile->getExtension());
	}

	public function testGetClientType()
	{
		$this->assertEquals('foo/bar', $this->uploadedFile->getClientType());
	}

	public function testGetType()
	{
		$this->assertEquals('image/png', $this->uploadedFile->getType());
	}

	public function testGetError()
	{
		$this->assertEquals(0, $this->uploadedFile->getError());
	}

	public function testGetErrorMessage()
	{
		$this->assertEquals('', $this->uploadedFile->getErrorMessage());
	}

	public function testGetName()
	{
		$this->assertEquals('logo.jpg', $this->uploadedFile->getName());
	}

	public function testGetSize()
	{
		$this->assertEquals(19878, $this->uploadedFile->getSize());
	}

	public function testGetTmpName()
	{
		$this->assertEquals(__DIR__ . '/files/logo.png', $this->uploadedFile->getTmpName());
	}

	public function testIsMoved()
	{
		$this->assertFalse($this->uploadedFile->isMoved());
	}

	public function testIsValid()
	{
		$this->assertFalse($this->uploadedFile->isValid());
	}

	public function testMove()
	{
		$this->assertFalse($this->uploadedFile->move('/tmp/foo'));
	}
}
