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
    /**
     * Restricts the URLs which can be used in a document's `<base>` element.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/base-uri
     */
    public const string baseUri = 'base-uri';
    /**
     * Defines the valid sources for web workers and nested browsing contexts
     * loaded using elements such as `<frame>` and `<iframe>`.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/child-src
     */
    public const string childSrc = 'child-src';
    /**
     * Restricts the URLs which can be loaded using script interfaces.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/connect-src
     */
    public const string connectSrc = 'connect-src';
    /**
     * Serves as a fallback for the other fetch directives.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/default-src
     */
    public const string defaultSrc = 'default-src';
    /**
     * Specifies valid sources for fonts loaded using `@font-face`.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/font-src
     */
    public const string fontSrc = 'font-src';
    /**
     * Restricts the URLs which can be used as the target of a form submissions
     * from a given context.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/form-action
     */
    public const string formAction = 'form-action';
    /**
     * Specifies valid parents that may embed a page using `<frame>`, `<iframe>`,
     * `<object>`, `<embed>`, or `<applet>`.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-ancestors
     */
    public const string frameAncestors = 'frame-ancestors';
    /**
     * Specifies valid sources for nested browsing contexts loading using
     * elements such as `<frame>` and `<iframe>`.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-src
     */
    public const string frameSrc = 'frame-src';
    /**
     * Specifies valid sources of images and favicons.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/img-src
     */
    public const string imgSrc = 'img-src';
    /**
     * Specifies valid sources of application manifest files.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/manifest-src
     */
    public const string manifestSrc = 'manifest-src';
    /**
     * Specifies valid sources for loading media using the `<audio>`, `<video>`
     * and `<track>` elements.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/media-src
     */
    public const string mediaSrc = 'media-src';
    /**
     * Restricts the URLs to which a document can initiate navigation by any
     * means, including `<form>` (if form-action is not specified), `<a>`,
     * `window.location`, `window.open`, etc.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/navigate-to
     */
    public const string navigateTo = 'navigate-to';
    /**
     * Specifies valid sources for the `<object>`, `<embed>`, and `<applet>`
     * elements.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/object-src
     */
    public const string objectSrc = 'object-src';
    /**
     * Restricts the set of plugins that can be embedded into a document by
     * limiting the types of resources which can be loaded.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/plugin-types
     * @deprecated
     */
    public const string pluginTypes = 'plugin-types';
    /**
     * Specifies valid sources to be prefetched or prerendered.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/prefetch-src
     * @deprecated
     */
    public const string prefetchSrc = 'prefetch-src';
    /**
     * Fires a SecurityPolicyViolationEvent.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/report-to
     */
    public const string reportTo = 'report-to';
    /**
     * Instructs the user agent to report attempts to violate the Content
     * Security Policy. These violation reports consist of JSON documents sent
     * via an HTTP POST request to the specified URI.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/report-uri
     * @deprecated
     */
    public const string reportUri = 'report-uri';
    /**
     * Enables a sandbox for the requested resource similar to the `<iframe>`
     * sandbox attribute.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/sandbox
     */
    public const string sandbox = 'sandbox';
    /**
     * Specifies valid sources for JavaScript and WebAssembly resources.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src
     */
    public const string scriptSrc = 'script-src';
    /**
     * Specifies valid sources for JavaScript inline event handlers.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src-attr
     */
    public const string scriptSrcAttr = 'script-src-attr';
    /**
     * Specifies valid sources for JavaScript `<script>` elements.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src-elem
     */
    public const string scriptSrcElem = 'script-src-elem';
    /**
     * Specifies valid sources for stylesheets.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/style-src
     */
    public const string styleSrc = 'style-src';
    /**
     * Specifies valid sources for inline styles applied to individual DOM
     * elements.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/style-src-attr
     */
    public const string styleSrcAttr = 'style-src-attr';
    /**
     * Specifies valid sources for stylesheets `<style>` elements and `<link>`
     * elements with `rel="stylesheet"`.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/style-src-elem
     */
    public const string styleSrcElem = 'style-src-elem';
    /**
     * Instructs user agents to treat all of a site's insecure URLs (those
     * served over HTTP) as though they have been replaced with secure URLs
     * (those served over HTTPS). This directive is intended for websites with
     * large numbers of insecure legacy URLs that need to be rewritten.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/upgrade-insecure-requests
     */
    public const string upgradeInsecureRequests = 'upgrade-insecure-requests';
    /**
     * Specifies valid sources for Worker, SharedWorker, or ServiceWorker
     * scripts.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/worker-src
     */
    public const string workerSrc = 'worker-src';
    /**
     * @var array<string,array<string>>
     */
    protected array $directives = [];

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
        if (empty($this->directives)) {
            throw new LogicException('No CSP directive has been set');
        }
        $directives = [];
        foreach ($this->directives as $name => $values) {
            $values = \implode(' ', $values);
            $directive = $name . ' ' . $values;
            $directives[] = \trim($directive);
        }
        return \implode('; ', $directives) . ';';
    }

    /**
     * @param string $directive
     * @param array<string>|string $values
     *
     * @return static
     */
    public function addValues(string $directive, array | string $values) : static
    {
        $directive = \strtolower($directive);
        $values = (array) $values;
        $this->directives[$directive] ??= [];
        foreach ($values as $value) {
            $this->directives[$directive][] = $this->sanitizeValue($value);
        }
        return $this;
    }

    protected function sanitizeValue(string $value) : string
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
        return \trim($value);
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
            $value = $this->sanitizeValue($value);
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
     * @return array<string>|null
     */
    public function getDirective(string $name) : array | null
    {
        return $this->directives[\strtolower($name)] ?? null;
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
        $this->addValues($type, "'nonce-{$nonce}'");
        return $nonce;
    }

    protected function getNonceAttr(string $type) : string
    {
        $nonce = match ($type) {
            static::scriptSrc => $this->addNonce(static::scriptSrc),
            static::styleSrc => $this->addNonce(static::styleSrc),
            default => throw new InvalidArgumentException(
                'Invalid CSP directive: ' . $type
            ),
        };
        return ' nonce="' . $nonce . '"';
    }

    /**
     * Creates a nonce, adds it to the script-src directive, and returns the
     * attribute to be inserted into the script tag.
     *
     * @return string the nonce attribute
     */
    public function getScriptNonceAttr() : string
    {
        return $this->getNonceAttr(static::scriptSrc);
    }

    /**
     * Creates a nonce, adds it to the style-src directive, and returns the
     * attribute to be inserted into the style tag.
     *
     * @return string the nonce attribute
     */
    public function getStyleNonceAttr() : string
    {
        return $this->getNonceAttr(static::styleSrc);
    }

    /**
     * @param string $html
     *
     * @return array<string>
     */
    public static function getStyleHashes(string $html) : array
    {
        return static::makeHashes(static::getStyleContents($html));
    }

    /**
     * @see https://stackoverflow.com/a/72636724
     * @see https://stackoverflow.com/a/50124875
     *
     * @param string $html
     *
     * @return array<string>
     */
    public static function getStyleContents(string $html) : array
    {
        \preg_match_all(
            '#<style[\w="\'\s-]*>([^<]+)</style>#i',
            $html,
            $matches
        );
        return $matches[1];
    }

    /**
     * @param string $html
     *
     * @return array<string>
     */
    public static function getScriptHashes(string $html) : array
    {
        return static::makeHashes(static::getScriptContents($html));
    }

    /**
     * @param string $html
     *
     * @return array<string>
     */
    public static function getScriptContents(string $html) : array
    {
        \preg_match_all(
            '#<script[\w="\'\s-]*>([^<]+)</script>#i',
            $html,
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
     * @param string $content
     *
     * @return string
     */
    public static function makeHash(string $algo, string $content) : string
    {
        $content = \hash($algo, $content, true);
        $content = \base64_encode($content);
        return $algo . '-' . $content;
    }
}
