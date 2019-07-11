<?php namespace Framework\HTTP;

/**
 * Interface RequestInterface.
 */
interface RequestInterface
{
	public function getProtocol() : string;

	public function getMethod() : string;

	public function getURL() : URL;

	public function getHeader(string $name) : ?string;

	public function getHeaders() : array;

	public function getBody() : string;
}
