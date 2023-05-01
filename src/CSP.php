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
use LogicException;

/**
 * Class CSP.
 *
 * @see https://content-security-policy.com/
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy
 *
 * @package http
 */
class CSP implements \Stringable
{
    public const baseUri = 'base-uri';
    public const childSrc = 'child-src';
    public const connectSrc = 'connect-src';
    public const defaultSrc = 'default-src';
    public const fontSrc = 'font-src';
    public const formAction = 'form-action';
    public const frameAncestors = 'frame-ancestors';
    public const frameSrc = 'frame-src';
    public const imgSrc = 'img-src';
    public const manifestSrc = 'manifest-src';
    public const mediaSrc = 'media-src';
    public const navigateTo = 'navigate-to';
    public const objectSrc = 'object-src';
    public const pluginTypes = 'plugin-types';
    public const prefetchSrc = 'prefetch-src';
    public const reportTo = 'report-to';
    public const reportUri = 'report-uri';
    public const sandbox = 'sandbox';
    public const scriptSrc = 'script-src';
    public const styleSrc = 'style-src';
    public const workerSrc = 'worker-src';
    protected bool $enabled = true;
    /**
     * @var array<string,array<string>>
     */
    protected array $directives = [];
    /**
     * @var array<string>
     */
    protected array $nonces = [];

    /**
     * @param array<string,array<string>> $directives
     */
    public function __construct(array $directives = [])
    {
        if ($directives) {
            $this->setDirectives($directives);
        }
    }

    public function __toString() : string
    {
        return $this->render();
    }

    public function render() : string
    {
        if ( ! $this->isEnabled()) {
            return '';
        }
        if (empty($this->directives)) {
            throw new LogicException('No CSP directive has been set');
        }
        $directives = [];
        foreach ($this->directives as $name => $values) {
            $directives[] = $name . ' ' . \implode(' ', $values);
        }
        return \implode('; ', $directives) . ';';
    }

    public function enable() : static
    {
        $this->enabled = true;
        return $this;
    }

    public function disable() : static
    {
        $this->enabled = false;
        return $this;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    /**
     * @param string $directive
     * @param array<string>|string $values
     *
     * @return static
     */
    public function addOptions(string $directive, array | string $values) : static
    {
        $directive = \strtolower($directive);
        $values = (array) $values;
        foreach ($values as $value) {
            $this->directives[$directive][] = $this->getValue($value);
        }
        return $this;
    }

    protected function getValue(string $value) : string
    {
        if (\in_array($value, [
                'none',
                'self',
                'strict-dynamic',
                'unsafe-eval',
                'unsafe-hashes',
                'unsafe-inline',
            ])
            || \str_starts_with($value, 'nonce-')
            || \str_starts_with($value, 'sha256-')
            || \str_starts_with($value, 'sha384-')
            || \str_starts_with($value, 'sha512-')
        ) {
            return "'{$value}'";
        }
        return $value;
    }

    /**
     * @param string $name
     * @param array<string>|string $values
     *
     * @return static
     */
    public function setDirective(string $name, array | string $values) : static
    {
        $values = (array) $values;
        foreach ($values as &$value) {
            $value = $this->getValue($value);
        }
        unset($value);
        $this->directives[\strtolower($name)] = $values;
        return $this;
    }

    /**
     * @param array<string,array<string>> $directives
     *
     * @return static
     */
    public function setDirectives(array $directives) : static
    {
        foreach ($directives as $name => $values) {
            $this->setDirective($name, $values);
        }
        return $this;
    }

    /**
     * @param string $name
     *
     * @return array<string>
     */
    public function getDirective(string $name) : array
    {
        return $this->directives[\strtolower($name)] ?? [];
    }

    /**
     * @return array<string,array<string>>
     */
    public function getDirectives() : array
    {
        return $this->directives;
    }

    public function removeDirective(string $name) : static
    {
        unset($this->directives[\strtolower($name)]);
        return $this;
    }

    /**
     * @param string $type
     *
     * @see https://content-security-policy.com/nonce/
     *
     * @return string
     */
    protected function addNonce(string $type) : string
    {
        $nonce = \bin2hex(\random_bytes(8));
        $this->nonces[] = $nonce;
        $this->addOptions($type, "'nonce-{$nonce}'");
        return $nonce;
    }

    public function addScriptSrcNonce() : string
    {
        return $this->addNonce(static::scriptSrc);
    }

    public function addStyleSrcNonce() : string
    {
        return $this->addNonce(static::styleSrc);
    }

    protected function getNonceAttr(string $type) : string
    {
        if ( ! $this->isEnabled()) {
            return '';
        }
        $nonce = match ($type) {
            static::scriptSrc => $this->addScriptSrcNonce(),
            static::styleSrc => $this->addStyleSrcNonce(),
            default => throw new InvalidArgumentException(
                'Invalid CSP directive: ' . $type
            ),
        };
        return ' nonce="' . $nonce . '"';
    }

    public function getScriptNonceAttr() : string
    {
        return $this->getNonceAttr(static::scriptSrc);
    }

    public function getStyleNonceAttr() : string
    {
        return $this->getNonceAttr(static::styleSrc);
    }

    /**
     * @return array<string>
     */
    public function getNonces() : array
    {
        return $this->nonces;
    }

    public function removeNonceAttributes(string $contents) : string
    {
        $attributes = [];
        foreach ($this->getNonces() as $nonce) {
            $attributes[' nonce="' . $nonce . '"'] = '';
        }
        return \strtr($contents, $attributes);
    }

    /**
     * @param string $contents
     *
     * @return array<string>
     */
    public static function getHashesOfStyles(string $contents) : array
    {
        return static::makeHashes(static::getContentsOfStyles($contents));
    }

    /**
     * @see https://stackoverflow.com/a/72636724
     * @see https://stackoverflow.com/a/50124875
     *
     * @param string $contents
     *
     * @return array<string>
     */
    public static function getContentsOfStyles(string $contents) : array
    {
        \preg_match_all(
            '#<style[\w="\'\s-]*>([^<]+)</style>#i',
            $contents,
            $matches
        );
        return $matches[1];
    }

    /**
     * @return array<string>
     */
    public static function getHashesOfScripts(string $contents) : array
    {
        return static::makeHashes(static::getContentsOfScripts($contents));
    }

    /**
     * @param string $contents
     *
     * @return array<string>
     */
    public static function getContentsOfScripts(string $contents) : array
    {
        \preg_match_all(
            '#<script[\w="\'\s-]*>([^<]+)</script>#i',
            $contents,
            $matches
        );
        return $matches[1];
    }

    /**
     * @param array<string> $contents
     * @param string $algo
     *
     * @return array<string>
     */
    public static function makeHashes(
        array $contents,
        string $algo = 'sha256'
    ) : array {
        $hashes = [];
        foreach ($contents as $content) {
            $hashes[] = static::makeHash($algo, $content);
        }
        return $hashes;
    }

    /**
     * @see https://content-security-policy.com/hash/
     * @see https://security.stackexchange.com/q/58789
     *
     * @param string $algo
     * @param string $contents
     *
     * @return string
     */
    public static function makeHash(string $algo, string $contents) : string
    {
        $contents = \hash($algo, $contents, true);
        $contents = \base64_encode($contents);
        return $algo . '-' . $contents;
    }
}
