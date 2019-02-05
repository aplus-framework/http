<?php namespace Framework\HTTP;

/**
 * Interface RequestInterface
 *
 * @package Framework\HTTP
 */
interface RequestInterface
{
	/**
	 * @return string
	 */
	public function getMethod(): string;

	/**
	 * @param bool $parse
	 *
	 * @return string|\Framework\HTTP\URL
	 */
	public function getURL(bool $parse = false);
}

