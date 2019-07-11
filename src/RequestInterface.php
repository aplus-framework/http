<?php namespace Framework\HTTP;

/**
 * Interface RequestInterface.
 */
interface RequestInterface
{
	public function getProtocol() : string;

	public function getMethod() : string;

	public function getURL() : URL;

	public function getHeader(string $name, bool $first) : ?string;

	public function getHeaders(string $name) : array;

	public function getAllHeaders() : array;

	public function getBody() : string;
}
