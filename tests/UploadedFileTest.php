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

use Framework\HTTP\UploadedFile;
use PHPUnit\Framework\TestCase;

final class UploadedFileTest extends TestCase
{
    /**
     * @var array<string,int|string>
     */
    protected array $file = [
        'name' => 'logo.jpg',
        'type' => 'foo/bar',
        'size' => 19878,
        'tmp_name' => __DIR__ . '/files/logo.png',
        'error' => \UPLOAD_ERR_OK,
    ];
    /**
     * @var array<string,int|string>
     */
    protected array $file2 = [
        'name' => 'file.txt',
        'full_path' => 'foo/bar/file.txt',
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

    public function testGetClientExtension() : void
    {
        self::assertSame('jpg', $this->uploadedFile->getClientExtension());
        self::assertSame('txt', $this->uploadedFile2->getClientExtension());
    }

    public function testGetExtension() : void
    {
        self::assertSame('png', $this->uploadedFile->getExtension());
        self::assertSame('png', $this->uploadedFile->getExtension());
        self::assertSame('txt', $this->uploadedFile2->getExtension());
    }

    public function testGetClientType() : void
    {
        self::assertSame('foo/bar', $this->uploadedFile->getClientType());
        self::assertSame('foo/baz', $this->uploadedFile2->getClientType());
    }

    public function testGetType() : void
    {
        self::assertSame('image/png', $this->uploadedFile->getType());
        self::assertSame('image/png', $this->uploadedFile->getType());
        self::assertSame('text/plain', $this->uploadedFile2->getType());
        self::assertSame('text/plain', $this->uploadedFile2->getType());
    }

    public function testGetError() : void
    {
        self::assertSame(\UPLOAD_ERR_OK, $this->uploadedFile->getError());
        self::assertSame(\UPLOAD_ERR_CANT_WRITE, $this->uploadedFile2->getError());
    }

    public function testGetErrorMessage() : void
    {
        self::assertSame(
            'There is no error, the file uploaded with success.',
            $this->uploadedFile->getErrorMessage()
        );
        self::assertSame(
            'Failed to write file to disk.',
            $this->uploadedFile2->getErrorMessage()
        );
    }

    public function testGetName() : void
    {
        self::assertSame('logo.jpg', $this->uploadedFile->getName());
        self::assertSame('file.txt', $this->uploadedFile2->getName());
    }

    public function testGetFullPath() : void
    {
        self::assertSame('logo.jpg', $this->uploadedFile->getFullPath());
        self::assertSame('foo/bar/file.txt', $this->uploadedFile2->getFullPath());
    }

    public function testGetSize() : void
    {
        self::assertSame(19878, $this->uploadedFile->getSize());
        self::assertSame(3, $this->uploadedFile2->getSize());
    }

    public function testGetTmpName() : void
    {
        self::assertSame(__DIR__ . '/files/logo.png', $this->uploadedFile->getTmpName());
        self::assertSame(__DIR__ . '/files/file.txt', $this->uploadedFile2->getTmpName());
    }

    public function testIsMoved() : void
    {
        self::assertFalse($this->uploadedFile->isMoved());
        self::assertFalse($this->uploadedFile2->isMoved());
    }

    public function testIsValid() : void
    {
        self::assertFalse($this->uploadedFile->isValid());
        self::assertFalse($this->uploadedFile2->isValid());
    }

    public function testMove() : void
    {
        self::assertFalse($this->uploadedFile->move('/tmp/foo'));
        self::assertFalse($this->uploadedFile2->move('/tmp/foo2'));
    }
}
