<?php namespace Framework\HTTP;

/**
 * Interface ResponseInterface.
 */
interface ResponseInterface
{
	public function getProtocol() : string;

	public function getStatusCode() : int;

	public function getStatusReason() : string;

	public function getStatusLine() : string;

	public function getHeader(string $name, int $index = -1) : ?string;

	public function getHeaders(string $name) : array;

	public function getAllHeaders() : array;

	public function getBody() : string;
}
