<?php namespace Tests\HTTP;

use Framework\HTTP\UploadedFile;
use PHPUnit\Framework\TestCase;

class UploadedFileTest extends TestCase
{
	/**
	 * @var \Framework\HTTP\UploadedFile;
	 */
	protected $uploadedFile;
	/**
	 * @var array
	 */
	protected $file = [
		'name'     => 'logo.png',
		'type'     => 'foo/bar',
		'size'     => 19878,
		'tmp_name' => __DIR__ . '/files/logo.png',
		'error'    => 0,
	];

	protected function setUp()
	{
		$this->uploadedFile = new UploadedFile($this->file);
	}

	public function testGetName()
	{
		$this->assertEquals($this->file['name'], $this->uploadedFile->getName());
	}

	public function testGetTmpName()
	{
		$this->assertEquals($this->file['tmp_name'], $this->uploadedFile->getTmpName());
	}

	public function testGetSize()
	{
		$this->assertEquals($this->file['size'], $this->uploadedFile->getSize());
	}

	public function testGetType()
	{
		$this->assertEquals('image/png', $this->uploadedFile->getType());
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

	public function testGetClientExtension()
	{
		$this->assertEquals('png', $this->uploadedFile->getClientExtension());
	}

	public function testMove()
	{
		$this->assertEquals(false, $this->uploadedFile->move('/tmp/foo'));
	}

	public function testIsMoved()
	{
		$this->assertEquals(false, $this->uploadedFile->isMoved());
	}

	public function testIsValid()
	{
		$this->assertEquals(false, $this->uploadedFile->isValid());
	}
}
