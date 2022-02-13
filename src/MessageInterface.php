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

use Stringable;

/**
 * Interface MessageInterface.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP
 *
 * @package http
 */
interface MessageInterface extends Stringable
{
    public function getProtocol() : string;

    public function getStartLine() : string;

    public function getHeader(string $name) : ?string;

    public function hasHeader(string $name, string $value = null) : bool;

    /**
     * @return array<string,string>
     */
    public function getHeaders() : array;

    public function getBody() : string;
}
