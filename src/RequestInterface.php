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

/**
 * Interface RequestInterface.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#http_requests
 *
 * @package http
 */
interface RequestInterface extends MessageInterface
{
    public function getMethod() : string;

    public function isMethod(string $method) : bool;

    public function getUrl() : URL;
}
