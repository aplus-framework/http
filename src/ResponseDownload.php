<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

/**
 * Trait ResponseDownload.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc7233
 *
 * @property Request $request
 *
 * @package http
 */
trait ResponseDownload
{
    private string $filepath;
    private int $filesize;
    private bool $acceptRanges = true;
    /**
     * @var array<int,array<int,int>>|false
     */
    private array | false $byteRanges = [];
    private string $sendType = 'normal';
    private string $boundary;
    /**
     * @var resource
     */
    private $handle;
    private int $delay = 0;
    private int $readLength = 1024;

    /**
     * Sets a file to download/stream.
     *
     * @param string $filepath
     * @param bool $inline Set Content-Disposition header as "inline". Browsers
     * load the file in the window. Set true to allow video or audio streams
     * @param bool $acceptRanges Set Accept-Ranges header to "bytes". Allow
     * partial downloads, media players to move the time position forward and
     * back and download managers to continue/download multi-parts
     * @param int $delay Delay between flushs in microseconds
     * @param int $readLength Bytes read by flush
     * @param string|null $filename A custom filename
     *
     * @throws InvalidArgumentException If invalid file path
     * @throws RuntimeException If can not get the file size or modification time
     *
     * @return static
     */
    public function setDownload(
        string $filepath,
        bool $inline = false,
        bool $acceptRanges = true,
        int $delay = 0,
        int $readLength = 1024,
        ?string $filename = null
    ) : static {
        $realpath = \realpath($filepath);
        if ($realpath === false || ! \is_file($realpath)) {
            throw new InvalidArgumentException('Invalid file path: ' . $filepath);
        }
        $this->filepath = $realpath;
        $this->delay = $delay;
        $this->readLength = $readLength;
        $filesize = @\filesize($this->filepath);
        if ($filesize === false) {
            throw new RuntimeException(
                "Could not get the file size of '{$this->filepath}'"
            );
        }
        $this->filesize = $filesize;
        $filemtime = \filemtime($this->filepath);
        if ($filemtime === false) {
            throw new RuntimeException(
                "Could not get the file modification time of '{$this->filepath}'"
            );
        }
        $this->setHeader(ResponseHeader::LAST_MODIFIED, \gmdate(\DATE_RFC7231, $filemtime));
        $filename ??= \basename($filepath);
        $filename = \htmlspecialchars($filename, \ENT_QUOTES | \ENT_HTML5);
        $filename = \strtr($filename, ['/' => '_', '\\' => '_']);
        $this->setHeader(
            Header::CONTENT_DISPOSITION,
            $inline ? 'inline' : \sprintf('attachment; filename="%s"', $filename)
        );
        $this->setAcceptRanges($acceptRanges);
        if ($acceptRanges) {
            $rangeLine = $this->request->getHeader(RequestHeader::RANGE);
            if ($rangeLine) {
                $this->prepareRange($rangeLine);
                return $this;
            }
        }
        $this->setHeader(Header::CONTENT_LENGTH, (string) $this->filesize);
        $this->setHeader(
            Header::CONTENT_TYPE,
            \mime_content_type($this->filepath) ?: 'application/octet-stream'
        );
        $this->sendType = 'normal';
        return $this;
    }

    private function prepareRange(string $rangeLine) : void
    {
        $this->byteRanges = $this->parseByteRange($rangeLine);
        if ($this->byteRanges === false) {
            // https://datatracker.ietf.org/doc/html/rfc7233#section-4.2
            $this->setStatus(Status::RANGE_NOT_SATISFIABLE);
            $this->setHeader(Header::CONTENT_RANGE, '*/' . $this->filesize);
            return;
        }
        $this->setStatus(Status::PARTIAL_CONTENT);
        if (\count($this->byteRanges) === 1) {
            $this->setSinglePart(...$this->byteRanges[0]);
            return;
        }
        $this->setMultiPart(...$this->byteRanges);
    }

    private function setAcceptRanges(bool $acceptRanges) : void
    {
        $this->acceptRanges = $acceptRanges;
        $this->setHeader(
            ResponseHeader::ACCEPT_RANGES,
            $acceptRanges ? 'bytes' : 'none'
        );
    }

    /**
     * Parse the HTTP Range Header line.
     *
     * Returns arrays of two indexes, representing first-byte-pos and last-byte-pos.
     * If return false, the Byte Ranges are invalid, so the Response must return
     * a 416 (Range Not Satisfiable) status.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7233#section-2.1
     * @see https://datatracker.ietf.org/doc/html/rfc7233#section-4.4
     *
     * @param string $line
     *
     * @return array<int,array<int,int>>|false
     *
     * @phpstan-ignore-next-line
     */
    #[Pure]
    private function parseByteRange(string $line) : array | false
    {
        if ( ! \str_starts_with($line, 'bytes=')) {
            return false;
        }
        $line = \substr($line, 6);
        $ranges = \explode(',', $line, 100);
        foreach ($ranges as &$range) {
            $range = \array_pad(\explode('-', $range, 2), 2, null);
            if ($range[0] === null || $range[1] === null) {
                return false;
            }
            if ($range[0] === '') {
                $range[1] = $this->validBytePos($range[1]);
                if ($range[1] === false) {
                    return false;
                }
                $range[0] = $this->filesize - $range[1];
                $range[1] = $this->filesize - 1;
                continue;
            }
            $range[0] = $this->validBytePos($range[0]);
            if ($range[0] === false) {
                return false;
            }
            if ($range[1] === '') {
                $range[1] = $this->filesize - 1;
                continue;
            }
            $range[1] = $this->validBytePos($range[1]);
            if ($range[1] === false) {
                return false;
            }
        }
        return $ranges; // @phpstan-ignore-line
    }

    /**
     * @param string $pos
     *
     * @return false|int
     */
    #[Pure]
    private function validBytePos(string $pos) : false | int
    {
        if ( ! \is_numeric($pos) || $pos < \PHP_INT_MIN || $pos > \PHP_INT_MAX) {
            return false;
        }
        if ($pos < 0 || $pos >= $this->filesize) {
            return false;
        }
        return (int) $pos;
    }

    private function setSinglePart(int $firstByte, int $lastByte) : void
    {
        $this->sendType = 'single';
        $this->setHeader(
            Header::CONTENT_LENGTH,
            (string) ($lastByte - $firstByte + 1)
        );
        $this->setHeader(
            Header::CONTENT_TYPE,
            \mime_content_type($this->filepath) ?: 'application/octet-stream'
        );
        $this->setHeader(
            Header::CONTENT_RANGE,
            \sprintf('bytes %d-%d/%d', $firstByte, $lastByte, $this->filesize)
        );
    }

    private function sendSinglePart() : void
    {
        // @phpstan-ignore-next-line
        $this->readBuffer($this->byteRanges[0][0], $this->byteRanges[0][1]);
        //$this->readFile();
    }

    /**
     * @param array<int,int> ...$byteRanges
     */
    private function setMultiPart(array ...$byteRanges) : void
    {
        $this->sendType = 'multi';
        $this->boundary = \md5($this->filepath);
        $length = 0;
        $topLength = \strlen($this->getMultiPartTopLine());
        foreach ($byteRanges as $range) {
            $length += $topLength;
            $length += \strlen($this->getContentRangeLine($range[0], $range[1]));
            $length += $range[1] - $range[0] + 1;
        }
        $length += \strlen($this->getBoundaryLine());
        $this->setHeader(Header::CONTENT_LENGTH, (string) $length);
        $this->setHeader(
            Header::CONTENT_TYPE,
            "multipart/x-byteranges; boundary={$this->boundary}"
        );
    }

    private function sendMultiPart() : void
    {
        $topLine = $this->getMultiPartTopLine();
        foreach ((array) $this->byteRanges as $range) {
            echo $topLine;
            echo $this->getContentRangeLine($range[0], $range[1]); // @phpstan-ignore-line
            $this->readBuffer($range[0], $range[1]); // @phpstan-ignore-line
        }
        echo $this->getBoundaryLine();
        if ($this->inToString) {
            $this->appendBody('');
        }
    }

    #[Pure]
    private function getBoundaryLine() : string
    {
        return "\r\n--{$this->boundary}--\r\n";
    }

    #[Pure]
    private function getMultiPartTopLine() : string
    {
        return $this->getBoundaryLine()
            . "Content-Type: application/octet-stream\r\n";
    }

    #[Pure]
    private function getContentRangeLine(int $fistByte, int $lastByte) : string
    {
        return \sprintf(
            "Content-Range: bytes %d-%d/%d\r\n\r\n",
            $fistByte,
            $lastByte,
            $this->filesize
        );
    }

    private function readBuffer(int $firstByte, int $lastByte) : void
    {
        \fseek($this->handle, $firstByte);
        $bytesLeft = $lastByte - $firstByte + 1;
        while ($bytesLeft > 0 && ! \feof($this->handle)) {
            $bytesRead = $bytesLeft > $this->readLength ? $this->readLength : $bytesLeft;
            $bytesLeft -= $bytesRead;
            $this->flush($bytesRead);
            if (\connection_status() !== \CONNECTION_NORMAL) {
                break;
            }
        }
    }

    private function flush(int $length) : void
    {
        echo \fread($this->handle, $length); // @phpstan-ignore-line
        if ($this->inToString) {
            $this->appendBody('');
            return;
        }
        \ob_flush();
        \flush();
        if ($this->delay) {
            \usleep($this->delay);
        }
    }

    private function readFile() : void
    {
        while ( ! \feof($this->handle)) {
            $this->flush($this->readLength);
            if (\connection_status() !== \CONNECTION_NORMAL) {
                break;
            }
        }
    }

    /**
     * Tell if Response has a downloadable file.
     *
     * @return bool
     */
    #[Pure]
    public function hasDownload() : bool
    {
        return isset($this->filepath);
    }

    protected function sendDownload() : void
    {
        $handle = \fopen($this->filepath, 'rb');
        if ($handle === false) {
            throw new RuntimeException(
                "Could not open a resource for file '{$this->filepath}'"
            );
        }
        $this->handle = $handle;
        switch ($this->sendType) {
            case 'multi':
                $this->sendMultiPart();
                break;
            case 'single':
                $this->sendSinglePart();
                break;
            default:
                $this->readFile();
        }
        \fclose($this->handle);
    }
}
