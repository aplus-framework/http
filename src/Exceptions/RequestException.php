<?php namespace Framework\HTTP\Exceptions;

class RequestException extends \OutOfBoundsException
{
	public static function forInvalidHost(string $host)
	{
		return new self('Invalid Host: "' . $host . '".');
	}

	public static function forInvalidInputType(string $type)
	{
		return new self('Invalid input type: "' . $type . '".');
	}
}
