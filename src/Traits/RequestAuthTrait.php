<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP\Traits;

/**
 * Trait RequestAuthTrait.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Authentication
 *
 * @package http
 */
trait RequestAuthTrait
{
    /**
     * HTTP Authorization Header parsed.
     *
     * @var array<string,string|null>|null
     */
    protected ?array $auth = null;
    /**
     * @var string|null Basic, Bearer or Digest
     */
    protected ?string $authType = null;

    /**
     * Get the Authorization type.
     *
     * @return string|null Basic, Bearer, Digest or null for none
     */
    public function getAuthType() : ?string
    {
        if ($this->authType === null) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
            if ($auth) {
                $this->parseAuth($auth);
            }
        }
        return $this->authType;
    }

    /**
     * Get Basic authorization.
     *
     * @return array{
     *      username: string|null,
     *      password: string|null,
     * }|null Two keys: username and password
     */
    public function getBasicAuth() : ?array
    {
        // @phpstan-ignore-next-line
        return $this->getAuthType() === 'Basic'
            ? $this->auth
            : null;
    }

    /**
     * Get Bearer authorization.
     *
     * @return array{
     *      token: string|null,
     * }|null One key: token
     */
    public function getBearerAuth() : ?array
    {
        // @phpstan-ignore-next-line
        return $this->getAuthType() === 'Bearer'
            ? $this->auth
            : null;
    }

    /**
     * Get Digest authorization.
     *
     * @return array{
     *      username: string|null,
     *      realm: string|null,
     *      nonce: string|null,
     *      uri: string|null,
     *      response: string|null,
     *      opaque: string|null,
     *      qop: string|null,
     *      nc: string|null,
     *      cnonce: string|null,
     * }|null Nine keys: username, realm, nonce, uri, response, opaque, qop, nc, cnonce
     */
    public function getDigestAuth() : ?array
    {
        // @phpstan-ignore-next-line
        return $this->getAuthType() === 'Digest'
            ? $this->auth
            : null;
    }

    /**
     * @param string $authorization
     *
     * @return array<string,string|null>
     */
    protected function parseAuth(string $authorization) : array
    {
        $this->auth = [];
        [$type, $attributes] = \array_pad(\explode(' ', $authorization, 2), 2, null);
        if ($type === 'Basic') {
            $this->authType = $type;
            $this->auth = $this->parseBasicAuth($attributes);
        } elseif ($type === 'Bearer') {
            $this->authType = $type;
            $this->auth = $this->parseBearerAuth($attributes);
        } elseif ($type === 'Digest') {
            $this->authType = $type;
            $this->auth = $this->parseDigestAuth($attributes);
        }
        return $this->auth;
    }

    /**
     * @param string $attributes
     *
     * @return array{
     *      username: string|null,
     *      password: string|null,
     * }
     */
    protected function parseBasicAuth(string $attributes) : array
    {
        $data = [
            'username' => null,
            'password' => null,
        ];
        $attributes = \base64_decode($attributes);
        if ($attributes) {
            [
                $data['username'],
                $data['password'],
            ] = \array_pad(\explode(':', $attributes, 2), 2, null);
        }
        return $data;
    }

    /**
     * @param string $attributes
     *
     * @return array{
     *      token: string|null,
     * }
     */
    protected function parseBearerAuth(string $attributes) : array
    {
        $data = [
            'token' => null,
        ];
        if ($attributes) {
            $data['token'] = $attributes;
        }
        return $data;
    }

    /**
     * @param string $attributes
     *
     * @return array{
     *      username: string|null,
     *      realm: string|null,
     *      nonce: string|null,
     *      uri: string|null,
     *      response: string|null,
     *      opaque: string|null,
     *      qop: string|null,
     *      nc: string|null,
     *      cnonce: string|null,
     * }
     */
    protected function parseDigestAuth(string $attributes) : array
    {
        $data = [
            'username' => null,
            'realm' => null,
            'nonce' => null,
            'uri' => null,
            'response' => null,
            'opaque' => null,
            'qop' => null,
            'nc' => null,
            'cnonce' => null,
        ];
        \preg_match_all(
            '#(username|realm|nonce|uri|response|opaque|qop|nc|cnonce)=(?:([\'"])([^\2]+?)\2|([^\s,]+))#',
            $attributes,
            $matches,
            \PREG_SET_ORDER
        );
        foreach ($matches as $match) {
            if (isset($match[3])) {
                $data[$match[1]] = $match[3] ?: $match[4] ?? '';
            }
        }
        return $data;
    }
}
