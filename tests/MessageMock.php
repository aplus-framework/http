<?php
/*
 * This file is part of The Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\HTTP;

use Framework\HTTP\Cookie;
use Framework\HTTP\Message;

class MessageMock extends Message
{
	public function setProtocol(string $protocol)
	{
		return parent::setProtocol($protocol);
	}

	public function setHeader(string $name, string $value)
	{
		return parent::setHeader($name, $value);
	}

	public function setHeaders(array $headers)
	{
		return parent::setHeaders($headers);
	}

	public function removeHeader(string $name)
	{
		return parent::removeHeader($name);
	}

	public function removeHeaders()
	{
		return parent::removeHeaders();
	}

	public function setBody(?string $body)
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
