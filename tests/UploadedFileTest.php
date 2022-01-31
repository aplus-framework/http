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
    protected UploadedFileMock $uploadedFile;
    protected UploadedFileMock $uploadedFile2;

    protected function setUp() : void
    {
        $this->uploadedFile = new UploadedFileMock($this->file);
        $this->uploadedFile2 = new UploadedFileMock($this->file2);
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

    public function testSetErrorMessage() : void
    {
        $this->uploadedFile->setErrorMessage(\UPLOAD_ERR_OK);
        self::assertStringStartsWith(
            'There is no error',
            $this->uploadedFile->getErrorMessage()
        );
        $this->uploadedFile->setErrorMessage(\UPLOAD_ERR_INI_SIZE);
        self::assertStringStartsWith(
            'The uploaded file exceeds the upload_max_filesize',
            $this->uploadedFile->getErrorMessage()
        );
        $this->uploadedFile->setErrorMessage(\UPLOAD_ERR_FORM_SIZE);
        self::assertStringStartsWith(
            'The uploaded file exceeds the MAX_FILE_SIZE',
            $this->uploadedFile->getErrorMessage()
        );
        $this->uploadedFile->setErrorMessage(\UPLOAD_ERR_PARTIAL);
        self::assertStringStartsWith(
            'The uploaded file was only partially',
            $this->uploadedFile->getErrorMessage()
        );
        $this->uploadedFile->setErrorMessage(\UPLOAD_ERR_NO_FILE);
        self::assertStringStartsWith(
            'No file was uploaded',
            $this->uploadedFile->getErrorMessage()
        );
        $this->uploadedFile->setErrorMessage(\UPLOAD_ERR_NO_TMP_DIR);
        self::assertStringStartsWith(
            'Missing a temporary',
            $this->uploadedFile->getErrorMessage()
        );
        $this->uploadedFile->setErrorMessage(\UPLOAD_ERR_CANT_WRITE);
        self::assertStringStartsWith(
            'Failed to write file',
            $this->uploadedFile->getErrorMessage()
        );
        $this->uploadedFile->setErrorMessage(\UPLOAD_ERR_EXTENSION);
        self::assertStringStartsWith(
            'A PHP extension stopped',
            $this->uploadedFile->getErrorMessage()
        );
        $this->uploadedFile->setErrorMessage(42);
        self::assertStringStartsWith(
            'Unknown error',
            $this->uploadedFile->getErrorMessage()
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
        $files = [
            \sys_get_temp_dir() . '/test-1',
            \sys_get_temp_dir() . '/test-2',
            \sys_get_temp_dir() . '/test-3',
        ];
        foreach ($files as $file) {
            if (\is_file($file)) {
                \unlink($file);
            }
        }
        self::assertFalse($this->uploadedFile->move($files[0]));
        \touch($files[0]);
        $this->uploadedFile->isMoved = true;
        $this->uploadedFile->destination = $files[0];
        self::assertTrue($this->uploadedFile->move($files[1]));
        self::assertSame($files[1], $this->uploadedFile->getDestination());
        \touch($files[2]);
        self::assertFalse($this->uploadedFile->move($files[2]));
        self::assertSame($files[1], $this->uploadedFile->getDestination());
        self::assertTrue($this->uploadedFile->move($files[2], true));
        self::assertSame($files[2], $this->uploadedFile->getDestination());
    }
}
