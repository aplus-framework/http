<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use LogicException;

/**
 * Class AntiCSRF.
 *
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#synchronizer-token-pattern
 * @see https://stackoverflow.com/q/6287903/6027968
 * @see https://portswigger.net/web-security/csrf
 * @see https://www.netsparker.com/blog/web-security/protecting-website-using-anti-csrf-token/
 *
 * @package http
 */
class AntiCSRF
{
    protected string $tokenName = 'csrf_token';
    protected Request $request;
    protected bool $verified = false;
    protected bool $enabled = true;
    protected int $tokenBytesLength = 8;
    protected string $generateTokenFunction = 'base64_encode';
    /**
     * @var array<string>
     */
    protected array $generateTokenFunctions = [
        'base64_encode',
        'bin2hex',
        'md5',
    ];

    /**
     * AntiCSRF constructor.
     *
     * @param Request $request
     * @param int|null $tokenBytesLength
     * @param string|null $generateTokenFunction
     */
    public function __construct(
        Request $request,
        ?int $tokenBytesLength = null,
        ?string $generateTokenFunction = null,
    ) {
        if (\session_status() !== \PHP_SESSION_ACTIVE) {
            throw new LogicException('Session must be active to use AntiCSRF class');
        }
        $this->request = $request;
        if ($tokenBytesLength !== null) {
            $this->setTokenBytesLength($tokenBytesLength);
        }
        if ($generateTokenFunction !== null) {
            $this->setGenerateTokenFunction($generateTokenFunction);
        }
        if ($this->getToken() === null) {
            $this->setToken();
        }
    }

    public function setTokenBytesLength(int $length) : static
    {
        if ($length < 3) {
            throw new InvalidArgumentException(
                'AntiCSRF token bytes length must be greater than 2, ' . $length . ' given'
            );
        }
        $this->tokenBytesLength = $length;
        return $this;
    }

    #[Pure]
    public function getTokenBytesLength() : int
    {
        return $this->tokenBytesLength;
    }

    /**
     * Gets the anti-csrf token name.
     *
     * @return string
     */
    #[Pure]
    public function getTokenName() : string
    {
        return $this->tokenName;
    }

    /**
     * Sets the anti-csrf token name.
     *
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
     * Gets the anti-csrf token from the session.
     *
     * @return string|null
     */
    #[Pure]
    public function getToken() : ?string
    {
        return $_SESSION['$']['csrf_token'] ?? null;
    }

    /**
     * Sets the anti-csrf token into the session.
     *
     * @param string|null $token A custom anti-csrf token or null to generate one
     *
     * @return static
     */
    public function setToken(?string $token = null) : static
    {
        $_SESSION['$']['csrf_token'] = $token ?? $this->generateToken();
        return $this;
    }

    public function setGenerateTokenFunction(string $function) : static
    {
        if (!\in_array($function, $this->generateTokenFunctions, true)) {
            throw new InvalidArgumentException(
                'Invalid generate token function name: ' . $function
            );
        }
        $this->generateTokenFunction = $function;
        return $this;
    }

    #[Pure]
    public function getGenerateTokenFunction() : string
    {
        return $this->generateTokenFunction;
    }

    public function generateToken() : string
    {
        $bytes = \random_bytes($this->getTokenBytesLength()); // @phpstan-ignore-line
        return $this->getGenerateTokenFunction()($bytes); // @phpstan-ignore-line
    }

    /**
     * Gets the user token from the request input form.
     *
     * @return string|null
     */
    public function getUserToken() : ?string
    {
        $token = $this->request->getParsedBody($this->getTokenName());
        return \is_string($token) ? $token : null;
    }

    /**
     * Verifies the request input token, if the verification is enabled.
     * The verification always succeed on HTTP GET, HEAD and OPTIONS methods.
     * If verification is successful with other HTTP methods, a new token is
     * generated.
     *
     * @return bool
     */
    public function verify() : bool
    {
        if ($this->isEnabled() === false) {
            return true;
        }
        if ($this->isSafeMethod()) {
            return true;
        }
        if ($this->getUserToken() === null) {
            return false;
        }
        if (!$this->validate($this->getUserToken())) {
            return false;
        }
        if (!$this->isVerified()) {
            $this->setToken();
            $this->setVerified();
        }
        return true;
    }

    /**
     * Safe HTTP Request methods are: GET, HEAD and OPTIONS.
     *
     * @return bool
     */
    #[Pure]
    public function isSafeMethod() : bool
    {
        return \in_array($this->request->getMethod(), [
           Method::GET,
           Method::HEAD,
           Method::OPTIONS,
        ], true);
    }

    /**
     * Validates if a user token is equals the session token.
     *
     * This method can be used to validate tokens not received through forms.
     * For example: Through a request header, JSON, etc.
     *
     * @param string $userToken
     *
     * @return bool
     */
    public function validate(string $userToken) : bool
    {
        return \hash_equals($_SESSION['$']['csrf_token'], $userToken);
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
     * Gets the HTML form hidden input if the verification is enabled.
     *
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
     * Tells if the verification is enabled.
     *
     * @see AntiCSRF::verify()
     *
     * @return bool
     */
    #[Pure]
    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    /**
     * Enables the Anti CSRF verification.
     *
     * @see AntiCSRF::verify()
     *
     * @return static
     */
    public function enable() : static
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * Disables the Anti CSRF verification.
     *
     * @see AntiCSRF::verify()
     *
     * @return static
     */
    public function disable() : static
    {
        $this->enabled = false;
        return $this;
    }
}
