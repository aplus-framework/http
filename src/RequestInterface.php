<?php namespace Framework\HTTP;

/**
 * Interface RequestInterface.
 */
interface RequestInterface
{
	/**
	 * @return string
	 */
	public function getMethod() : string;

	/**
	 * @param bool $parse
	 *
	 * @return \Framework\HTTP\URL|string
	 */
	public function getURL(bool $parse = false);
}
