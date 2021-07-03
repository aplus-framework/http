<?php declare(strict_types=1);
/*
 * This file is part of The Framework HTTP Library.
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
 */
interface RequestInterface
{
	public function getProtocol() : string;

	public function getMethod() : string;

	public function getURL() : URL;

	public function getHeader(string $name) : ?string;

	/**
	 * @return array<string,string>
	 */
	public function getHeaders() : array;

	public function getBody() : string;
}
