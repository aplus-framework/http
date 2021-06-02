<?php namespace Framework\HTTP;

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
		$this->tokenName = \htmlspecialchars($tokenName, \ENT_QUOTES | \ENT_HTML5);
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

	protected function getUserToken() : ?string
	{
		return $this->request->getParsedBody($this->getTokenName());
	}

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
		if (\hash_equals($_SESSION['$']['csrf_token'], $this->getUserToken())) {
			if ( ! $this->isVerified()) {
				$this->setToken();
				$this->setVerified(true);
			}
			return true;
		}
		return false;
	}

	protected function isVerified() : bool
	{
		return $this->verified;
	}

	/**
	 * @param bool $status
	 *
	 * @return $this
	 */
	protected function setVerified(bool $status)
	{
		$this->verified = $status;
		return $this;
	}

	public function input() : string
	{
		if ($this->isEnabled() === false) {
			return '';
		}
		return '<input type="hidden" name="'
			. $this->getTokenName() . '" value="'
			. $this->getToken() . '">';
	}

	public function isEnabled() : bool
	{
		return $this->enabled;
	}

	public function enable()
	{
		$this->enabled = true;
		return $this;
	}

	public function disable()
	{
		$this->enabled = false;
		return $this;
	}
}
