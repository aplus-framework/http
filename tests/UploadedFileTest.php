<?php namespace Tests\HTTP;

use Framework\HTTP\UploadedFile;
use PHPUnit\Framework\TestCase;

class UploadedFileTest extends TestCase
{
	/**
	 * @var array
	 */
	protected $file = [
		'name' => 'logo.png',
		'type' => 'foo/bar',
		'size' => 19878,
		'tmp_name' => __DIR__ . '/files/logo.png',
		'error' => 0,
	];
	/**
	 * @var \Framework\HTTP\UploadedFile;
	 */
	protected $uploadedFile;

	protected function setUp()
	{
		$this->uploadedFile = new UploadedFile($this->file);
	}

	public function testGetClientExtension()
	{
		$this->assertEquals('png', $this->uploadedFile->getClientExtension());
	}

	public function testGetClientType()
	{
		$this->assertEquals($this->file['type'], $this->uploadedFile->getClientType());
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
		$this->assertEquals($this->file['name'], $this->uploadedFile->getName());
	}

	public function testGetSize()
	{
		$this->assertEquals($this->file['size'], $this->uploadedFile->getSize());
	}

	public function testGetTmpName()
	{
		$this->assertEquals($this->file['tmp_name'], $this->uploadedFile->getTmpName());
	}

	public function testGetType()
	{
		$this->assertEquals('image/png', $this->uploadedFile->getType());
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
