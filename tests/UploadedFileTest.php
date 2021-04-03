<?php namespace Tests\HTTP;

use Framework\HTTP\UploadedFile;
use PHPUnit\Framework\TestCase;

class UploadedFileTest extends TestCase
{
	protected array $file = [
		'name' => 'logo.jpg',
		'type' => 'foo/bar',
		'size' => 19878,
		'tmp_name' => __DIR__ . '/files/logo.png',
		'error' => \UPLOAD_ERR_OK,
	];
	protected array $file2 = [
		'name' => 'file.txt',
		'type' => 'foo/baz',
		'size' => 3,
		'tmp_name' => __DIR__ . '/files/file.txt',
		'error' => \UPLOAD_ERR_CANT_WRITE,
	];
	protected UploadedFile $uploadedFile;
	protected UploadedFile $uploadedFile2;

	protected function setUp() : void
	{
		$this->uploadedFile = new UploadedFile($this->file);
		$this->uploadedFile2 = new UploadedFile($this->file2);
	}

	public function testGetClientExtension()
	{
		$this->assertEquals('jpg', $this->uploadedFile->getClientExtension());
		$this->assertEquals('txt', $this->uploadedFile2->getClientExtension());
	}

	public function testGetExtension()
	{
		$this->assertEquals('png', $this->uploadedFile->getExtension());
		$this->assertEquals('png', $this->uploadedFile->getExtension());
		$this->assertEquals('txt', $this->uploadedFile2->getExtension());
	}

	public function testGetClientType()
	{
		$this->assertEquals('foo/bar', $this->uploadedFile->getClientType());
		$this->assertEquals('foo/baz', $this->uploadedFile2->getClientType());
	}

	public function testGetType()
	{
		$this->assertEquals('image/png', $this->uploadedFile->getType());
		$this->assertEquals('text/plain', $this->uploadedFile2->getType());
	}

	public function testGetError()
	{
		$this->assertEquals(\UPLOAD_ERR_OK, $this->uploadedFile->getError());
		$this->assertEquals(\UPLOAD_ERR_CANT_WRITE, $this->uploadedFile2->getError());
	}

	public function testGetErrorMessage()
	{
		$this->assertEquals('', $this->uploadedFile->getErrorMessage());
		$this->assertEquals('uploadErrorCantWrite', $this->uploadedFile2->getErrorMessage());
	}

	public function testGetName()
	{
		$this->assertEquals('logo.jpg', $this->uploadedFile->getName());
		$this->assertEquals('file.txt', $this->uploadedFile2->getName());
	}

	public function testGetSize()
	{
		$this->assertEquals(19878, $this->uploadedFile->getSize());
		$this->assertEquals(3, $this->uploadedFile2->getSize());
	}

	public function testGetTmpName()
	{
		$this->assertEquals(__DIR__ . '/files/logo.png', $this->uploadedFile->getTmpName());
		$this->assertEquals(__DIR__ . '/files/file.txt', $this->uploadedFile2->getTmpName());
	}

	public function testIsMoved()
	{
		$this->assertFalse($this->uploadedFile->isMoved());
		$this->assertFalse($this->uploadedFile2->isMoved());
	}

	public function testIsValid()
	{
		$this->assertFalse($this->uploadedFile->isValid());
		$this->assertFalse($this->uploadedFile2->isValid());
	}

	public function testMove()
	{
		$this->assertFalse($this->uploadedFile->move('/tmp/foo'));
		$this->assertFalse($this->uploadedFile2->move('/tmp/foo2'));
	}
}
