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
 * Interface ResponseInterface.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#http_responses
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
 * @see https://datatracker.ietf.org/doc/html/rfc7231#section-6
 *
 * @package http
 */
interface ResponseInterface extends MessageInterface
{
    public function getStatusCode() : int;

    public function isStatusCode(int $code) : bool;

    public function getStatusReason() : string;

    public function getStatus() : string;
}
