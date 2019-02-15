<?php namespace Framework\HTTP\Exceptions;

class ResponseException extends \OutOfBoundsException
{
	public static function forHeadersSent()
	{
		return new self('Headers already is sent');
	}

	public static function forInvalidStatusCode(int $code)
	{
		return new self('Invalid status code "' . $code . '".');
	}

	public static function forJSONError($error)
	{
		return new self('JSON error: ' . $error);
	}

	public static function forResponseSent()
	{
		return new self('Response already is sent');
	}

	public static function forUnknowStatus(int $code)
	{
		return new self('Unknow status code "' . $code . '" must have a reason.');
	}
}
