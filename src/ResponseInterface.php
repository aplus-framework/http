<?php namespace Framework\HTTP;

/**
 * Interface ResponseInterface.
 */
interface ResponseInterface
{
	public function getProtocol() : string;

	public function getStatusCode() : int;

	public function getStatusReason() : string;

	public function getHeader(string $name) : ?string;

	public function getHeaders() : array;

	public function getBody() : string;
}
