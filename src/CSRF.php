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

use JetBrains\PhpStorm\Pure;
use LogicException;

/**
 * Class CSRF.
 *
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
 * @see https://stackoverflow.com/q/6287903/6027968
 * @see https://portswigger.net/web-security/csrf
 * @see https://www.netsparker.com/blog/web-security/protecting-website-using-anti-csrf-token/
 */
class CSRF
{
	protected string $tokenName = 'csrf_token';
	protected Request $request;
	protected bool $verified = false;
	protected bool $enabled = true;

	/**
	 * CSRF constructor.
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		if (\session_status() !== \PHP_SESSION_ACTIVE) {
			throw new LogicException('Session must be active to use CSRF class');
		}
		$this->request = $request;
		if ($this->getToken() === null) {
			$this->setToken();
		}
	}

	/**
	 * @return string
	 */
	#[Pure]
	public function getTokenName() : string
	{
		return $this->tokenName;
	}

	/**
	 * @param string $tokenName
	 *
	 * @return static
	 */
	public function setTokenName(string $tokenName) : static
	{
		$this->tokenName = \htmlspecialchars($tokenName, \ENT_QUOTES | \ENT_HTML5);
		return $this;
	}

	/**
	 * @return string|null
	 */
	#[Pure]
	public function getToken() : ?string
	{
		return $_SESSION['$']['csrf_token'] ?? null;
	}

	/**
	 * @return static
	 */
	protected function setToken() : static
	{
		$token = '';
		$alnum = 'abcdefghijklmnopqrstuvxywzABCDEFGHIJKLMNOPQRSTUVXYWZ0123456789';
		$max = \strlen($alnum) - 1;
		for ($i = 0; $i < 6; $i++) {
			$token .= $alnum[\rand(0, $max)];
		}
		$_SESSION['$']['csrf_token'] = \bin2hex($token);
		return $this;
	}

	protected function getUserToken() : ?string
	{
		return $this->request->getParsedBody($this->getTokenName());
	}

	/**
	 * @return bool
	 */
	public function verify() : bool
	{
		if ($this->isEnabled() === false) {
			return true;
		}
		if (\in_array($this->request->getMethod(), [
			'GET',
			'HEAD',
			'OPTIONS',
		], true)) {
			return true;
		}
		if ($this->getUserToken() === null) {
			return false;
		}
		if ( ! \hash_equals($_SESSION['$']['csrf_token'], $this->getUserToken())) {
			return false;
		}
		if ( ! $this->isVerified()) {
			$this->setToken();
			$this->setVerified();
		}
		return true;
	}

	#[Pure]
	protected function isVerified() : bool
	{
		return $this->verified;
	}

	/**
	 * @param bool $status
	 *
	 * @return static
	 */
	protected function setVerified(bool $status = true) : static
	{
		$this->verified = $status;
		return $this;
	}

	/**
	 * @return string
	 */
	#[Pure]
	public function input() : string
	{
		if ($this->isEnabled() === false) {
			return '';
		}
		return '<input type="hidden" name="'
			. $this->getTokenName() . '" value="'
			. $this->getToken() . '">';
	}

	/**
	 * @return bool
	 */
	#[Pure]
	public function isEnabled() : bool
	{
		return $this->enabled;
	}

	/**
	 * @return static
	 */
	public function enable() : static
	{
		$this->enabled = true;
		return $this;
	}

	/**
	 * @return static
	 */
	public function disable() : static
	{
		$this->enabled = false;
		return $this;
	}
}
