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
 * Interface ResponseInterface.
 */
interface ResponseInterface
{
	public function getProtocol() : string;

	public function getStatusCode() : int;

	public function getStatusReason() : string;

	public function getStatusLine() : string;

	public function getHeader(string $name) : ?string;

	/**
	 * @return array<string,string>
	 */
	public function getHeaders() : array;

	public function getBody() : string;
}
