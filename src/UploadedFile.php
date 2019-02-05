<?php namespace Framework\HTTP;

use Config\Services;

/**
 * Class UploadedFile
 *
 * @package Framework\HTTP
 */
class UploadedFile
{
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @todo Get a secure extension
	 * @var string
	 */
	protected $extension;
	/**
	 * @var string
	 */
	protected $clientExtension;
	/**
	 * @var string
	 */
	protected $type;
	/**
	 * @var string
	 */
	protected $clientType;
	/**
	 * @var string
	 */
	protected $tmpName;
	/**
	 * @var int
	 */
	protected $error;
	/**
	 * @var string
	 */
	protected $errorMessage;
	/**
	 * @var int
	 */
	protected $size;
	/**
	 * @var bool
	 */
	protected $isMoved = false;

	public function __construct(array $file)
	{
		$this->name       = $file['name'];
		$this->clientType = $file['type'];
		$this->tmpName    = $file['tmp_name'];
		$this->error      = $file['error'];
		$this->size       = $file['size'];
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getTmpName(): string
	{
		return $this->tmpName;
	}

	/**
	 * @return int
	 */
	public function getSize(): int
	{
		return $this->size;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		if ($this->type === null)
		{
			$finfo = \finfo_open(\FILEINFO_MIME_TYPE);

			if (\is_resource($finfo))
			{
				$this->type = \finfo_file($finfo, $this->tmpName);
				\finfo_close($finfo);
			}
			else
			{
				$this->type = '';
			}
		}

		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getClientType()
	{
		return $this->clientType;
	}

	/**
	 * @return string
	 */
	public function getClientExtension(): string
	{
		if ($this->clientExtension === null)
		{
			$this->clientExtension = (string)\pathinfo($this->getName(), \PATHINFO_EXTENSION);
		}

		return $this->clientExtension;
	}

	/**
	 * @return int
	 */
	public function getError(): int
	{
		return $this->error;
	}

	/**
	 * WARNING! This message should not be showed to the final user. Use Validation errors instead.
	 *
	 * @return string
	 */
	public function getErrorMessage(): string
	{
		if ($this->errorMessage === null)
		{
			$this->setErrorMessage($this->error);
		}

		return $this->errorMessage;
	}

	/**
	 * Moves an uploaded file to a new location
	 *
	 * @param string $destination The destination of the moved file
	 * @param bool   $overwrite
	 *
	 * @return bool
	 */
	public function move(string $destination, bool $overwrite = false): bool
	{
		if ($this->isMoved)
		{
			throw new \ErrorException('File already is moved.');
		}

		if ($overwrite === false && \file_exists($destination))
		{
			return false;
		}

		return $this->isMoved = \move_uploaded_file($this->tmpName, $destination);
	}

	public function isMoved(): bool
	{
		return $this->isMoved;
	}

	public function isValid(): bool
	{
		return $this->error === \UPLOAD_ERR_OK && \is_uploaded_file($this->tmpName);
	}

	/**
	 * @param int $code
	 *
	 * @see http://php.net/manual/en/features.file-upload.errors.php
	 */
	protected function setErrorMessage(int $code)
	{
		switch ($code)
		{
			case \UPLOAD_ERR_OK:
				$line = '';
				break;
			case \UPLOAD_ERR_INI_SIZE:
				$line = 'uploadErrorIniSize';
				break;
			case \UPLOAD_ERR_FORM_SIZE:
				$line = 'uploadErrorFormSize';
				break;
			case \UPLOAD_ERR_PARTIAL:
				$line = 'uploadErrorPartial';
				break;
			case \UPLOAD_ERR_NO_FILE:
				$line = 'uploadErrorNoFile';
				break;
			case \UPLOAD_ERR_NO_TMP_DIR:
				$line = 'uploadErrorNoTmpDir';
				break;
			case \UPLOAD_ERR_CANT_WRITE:
				$line = 'uploadErrorCantWrite';
				break;
			case \UPLOAD_ERR_EXTENSION:
				$line = 'uploadErrorExtension';
				break;
			default:
				$line = 'uploadErrorUnknown';
				break;
		}

		$this->errorMessage = empty($line)
			? ''
			: Services::language()->render('http', $line, [\esc($this->getName())]);
	}
}

