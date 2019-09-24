<?php namespace Framework\HTTP;

/**
 * Trait ResponseDownload.
 *
 * @see https://tools.ietf.org/html/rfc7233
 *
 * @property Request $request
 */
trait ResponseDownload
{
	/**
	 * @var string
	 */
	private $filepath;
	/**
	 * @var int
	 */
	private $filesize;
	/**
	 * @var bool
	 */
	private $acceptRanges = true;
	/**
	 * @var array
	 */
	private $byteRanges = [];
	/**
	 * @var string
	 */
	private $sendType = 'normal';
	/**
	 * @var string
	 */
	private $boundary;
	/**
	 * @var resource
	 */
	private $handle;
	/**
	 * @var int
	 */
	private $delay = 0;
	/**
	 * @var int
	 */
	private $readLength = 1024;

	/**
	 * Sets a file to download/stream.
	 *
	 * @param string $filepath
	 * @param bool   $inline       Set Content-Disposition header as "inline". Browsers load the
	 *                             file in the window. Set true to allow video or audio streams.
	 * @param bool   $acceptRanges Set Accept-Ranges header to "bytes". Allow partial downloads,
	 *                             media players to move the time position forward and back and
	 *                             download managers to continue/download multi-parts
	 * @param int    $delay        Delay between flushs in microseconds
	 * @param int    $readLength   Bytes read by flush
	 *
	 * @throws \InvalidArgumentException If invalid file path
	 *
	 * @return $this
	 */
	public function setDownload(
		string $filepath,
		bool $inline = false,
		bool $acceptRanges = true,
		int $delay = 0,
		int $readLength = 1024
	) {
		$this->filepath = \realpath($filepath);
		if ($filepath === false || ! \is_file($this->filepath)) {
			throw new \InvalidArgumentException('Invalid file path: ' . $filepath);
		}
		$this->delay = $delay;
		$this->readLength = $readLength;
		$this->filesize = \filesize($this->filepath);
		$filename = \basename($filepath);
		$this->setHeader('Last-Modified', \date(\DATE_RFC7231, \filemtime($this->filepath)));
		$this->setHeader(
			'Content-Disposition',
			$inline ? 'inline' : \sprintf('attachment; filename="%s"', $filename)
		);
		$this->setAcceptRanges($acceptRanges);
		if ($acceptRanges
			&& $rangeLine = $this->request->getHeader('Range')
		) {
			$this->prepareRange($rangeLine);
			return $this;
		}
		$this->setHeader('Content-Length', $this->filesize);
		$this->setHeader('Content-Type', \mime_content_type($this->filepath));
		$this->sendType = 'normal';
		return $this;
	}

	private function prepareRange(string $rangeLine)
	{
		$this->byteRanges = $this->parseByteRange($rangeLine);
		if ($this->byteRanges === false) {
			// https://tools.ietf.org/html/rfc7233#section-4.2
			$this->setStatusLine(416);
			$this->setHeader('Content-Range', '*/' . $this->filesize);
			return;
		}
		$this->setStatusLine(206);
		if (\count($this->byteRanges) === 1) {
			$this->setSinglePart(...$this->byteRanges[0]);
			return;
		}
		$this->setMultiPart(...$this->byteRanges);
	}

	private function setAcceptRanges(bool $acceptRanges)
	{
		$this->acceptRanges = $acceptRanges;
		$this->setHeader(
			'Accept-Ranges',
			$acceptRanges ? 'bytes' : 'none'
		);
	}

	/**
	 * Parse the HTTP Range Header line.
	 *
	 * Returns an array of two indexes representing first-byte-pos and last-byte-pos.
	 * If return false the Byte Ranges are invalid, the Response must return a 416 (Range Not
	 * Satisfiable) status.
	 *
	 * @see https://tools.ietf.org/html/rfc7233#section-2.1
	 * @see https://tools.ietf.org/html/rfc7233#section-4.4
	 *
	 * @param string $line
	 *
	 * @return array|false
	 */
	private function parseByteRange(string $line)
	{
		if (\strpos($line, 'bytes=') !== 0) {
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
		return $ranges;
	}

	/**
	 * @param string $pos
	 *
	 * @return false|int
	 */
	private function validBytePos(string $pos)
	{
		if ( ! \is_numeric($pos) || $pos < \PHP_INT_MIN || $pos > \PHP_INT_MAX) {
			return false;
		}
		if ($pos < 0 || $pos >= $this->filesize) {
			return false;
		}
		return (int) $pos;
	}

	private function setSinglePart(int $firstByte, int $lastByte)
	{
		$this->sendType = 'single';
		$this->setHeader('Content-Length', $lastByte - $firstByte + 1);
		$this->setHeader('Content-Type', \mime_content_type($this->filepath));
		$this->setHeader(
			'Content-Range',
			\sprintf('bytes %d-%d/%d', $firstByte, $lastByte, $this->filesize)
		);
	}

	private function sendSinglePart()
	{
		$this->readBuffer($this->byteRanges[0][0], $this->byteRanges[0][1]);
		$this->readFile();
	}

	private function setMultiPart(array ...$byteRanges)
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
		$this->setHeader('Content-Length', $length);
		$this->setHeader('Content-Type', "multipart/x-byteranges; boundary={$this->boundary}");
	}

	private function sendMultiPart()
	{
		$topLine = $this->getMultiPartTopLine();
		foreach ($this->byteRanges as $range) {
			echo $topLine;
			echo $this->getContentRangeLine($range[0], $range[1]);
			$this->readBuffer($range[0], $range[1]);
		}
		echo $this->getBoundaryLine();
	}

	private function getBoundaryLine() : string
	{
		return "\r\n--{$this->boundary}--\r\n";
	}

	private function getMultiPartTopLine() : string
	{
		return $this->getBoundaryLine()
			. "Content-Type: application/octet-stream\r\n";
	}

	private function getContentRangeLine(int $fistByte, int $lastByte) : string
	{
		return \sprintf(
			"Content-range: bytes %d-%d/%d\r\n\r\n",
			$fistByte,
			$lastByte,
			$this->filesize
		);
	}

	private function readBuffer(int $firstByte, int $lastByte)
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

	private function flush(int $length)
	{
		echo \fread($this->handle, $length);
		\ob_flush();
		\flush();
		if ($this->delay) {
			\usleep($this->delay);
		}
	}

	private function readFile()
	{
		while ( ! \feof($this->handle)) {
			$this->flush($this->readLength);
			if (\connection_status() !== \CONNECTION_NORMAL) {
				break;
			}
		}
	}

	public function hasDownload() : bool
	{
		return isset($this->filepath);
	}

	protected function sendDownload() : void
	{
		$this->handle = \fopen($this->filepath, 'rb');
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
		if ($this->handle) {
			\fclose($this->handle);
		}
	}
}
