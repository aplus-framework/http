<?php namespace Framework\HTTP\Exceptions;

class URLException extends \OutOfBoundsException
{
	public static function forInvalidHost($host)
	{
		return new self('Invalid URL Host: ' . $host);
	}

	public static function forInvalidPort($port)
	{
		return new self('Invalid URL Port: ' . $port);
	}

	public static function forInvalidURL($url)
	{
		return new self('Invalid URL: ' . $url);
	}
}
