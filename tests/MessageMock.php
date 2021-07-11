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
    public function setProtocol(string $protocol) : static
    {
        return parent::setProtocol($protocol);
    }

    public function setHeader(string $name, string $value) : static
    {
        return parent::setHeader($name, $value);
    }

    public function setHeaders(array $headers) : static
    {
        return parent::setHeaders($headers);
    }

    public function removeHeader(string $name) : static
    {
        return parent::removeHeader($name);
    }

    public function removeHeaders() : static
    {
        return parent::removeHeaders();
    }

    public function setBody(?string $body) : static
    {
        return parent::setBody($body);
    }

    public function setCookie(Cookie $cookie) : static
    {
        return parent::setCookie($cookie);
    }

    public function setCookies(array $cookies) : static
    {
        return parent::setCookies($cookies);
    }

    public function removeCookie(string $name) : static
    {
        return parent::removeCookie($name);
    }

    public function removeCookies(array $names) : static
    {
        return parent::removeCookies($names);
    }

    public function parseContentType() : ?string
    {
        return parent::parseContentType();
    }
}
