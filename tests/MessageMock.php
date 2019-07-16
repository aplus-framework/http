<?php namespace Tests\HTTP;

use Framework\HTTP\Cookie;
use Framework\HTTP\Message;

class MessageMock extends Message
{
	public function setProtocol(string $protocol)
	{
		return parent::setProtocol($protocol);
	}

	public function setHeader(string $name, string ...$values)
	{
		return parent::setHeader($name, ...$values);
	}

	public function setHeaders(array $headers)
	{
		return parent::setHeaders($headers);
	}

	public function addHeader(string $name, string $value)
	{
		return parent::addHeader($name, $value);
	}

	public function removeHeader(string $name, int $index = -1)
	{
		return parent::removeHeader($name, $index);
	}

	public function removeHeaders(string $name)
	{
		return parent::removeHeaders($name);
	}

	public function removeAllHeaders()
	{
		return parent::removeAllHeaders();
	}

	public function sendHeaders() : void
	{
		parent::sendHeaders();
	}

	public function setBody(string $body)
	{
		return parent::setBody($body);
	}

	public function setCookie(Cookie $cookie)
	{
		return parent::setCookie($cookie);
	}

	public function setCookies(array $cookies)
	{
		return parent::setCookies($cookies);
	}

	public function removeCookie(string $name)
	{
		return parent::removeCookie($name);
	}

	public function removeCookies(array $names)
	{
		return parent::removeCookies($names);
	}
}
