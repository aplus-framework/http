<?php namespace Framework\HTTP;

use LogicException;

/**
 * Class CSRF.
 *
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
 * @see https://stackoverflow.com/q/6287903/6027968
 * @see https://portswigger.net/web-security/csrf
 */
class CSRF
{
	protected string $tokenName = 'csrf_token';

	public function __construct()
	{
		if (\session_status() !== \PHP_SESSION_ACTIVE) {
			throw new LogicException('Session must be active to use CSRF class');
		}
		if ($this->getToken() === null) {
			$this->setToken();
		}
	}

	public function getTokenName() : string
	{
		return $this->tokenName;
	}

	/**
	 * @param string $tokenName
	 *
	 * @return $this
	 */
	public function setTokenName(string $tokenName)
	{
		$this->tokenName = \htmlspecialchars($tokenName, \ENT_QUOTES);
		return $this;
	}

	public function getToken() : ?string
	{
		return $_SESSION['$']['csrf_token'] ?? null;
	}

	/**
	 * @return $this
	 */
	protected function setToken()
	{
		$_SESSION['$']['csrf_token'] = \bin2hex(\random_bytes(32));
		return $this;
	}

	protected function getMethod() : string
	{
		$method = \filter_input(\INPUT_SERVER, 'REQUEST_METHOD');
		return \strtoupper($method);
	}

	protected function getUserToken() : ?string
	{
		return \filter_input(\INPUT_POST, $this->getTokenName());
	}

	public function verify() : bool
	{
		if (\in_array($this->getMethod(), [
			'GET',
			'HEAD',
			'OPTIONS',
		], true)) {
			return true;
		}
		if ($this->getUserToken() === null) {
			return false;
		}
		if (\hash_equals($_SESSION['$']['csrf_token'], $this->getUserToken())) {
			$this->setToken();
			return true;
		}
		return false;
	}

	public function input() : string
	{
		return '<input type="hidden" name="'
			. $this->getTokenName() . '" value="'
			. $this->getToken() . '">';
	}
}
