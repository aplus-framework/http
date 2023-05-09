<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\HTTP;

use Framework\HTTP\CSP;
use PHPUnit\Framework\TestCase;

final class CSPTest extends TestCase
{
    public function testConstructor() : void
    {
        $csp = new CSP();
        self::assertSame([], $csp->getDirectives());
        $csp = new CSP([
            CSP::defaultSrc => [
                'self',
            ],
        ]);
        self::assertSame([
            'default-src' => [
                "'self'",
            ],
        ], $csp->getDirectives());
    }

    public function testToString() : void
    {
        $csp = new CSP([
            CSP::defaultSrc => [
                'self',
            ],
            CSP::styleSrc => [
                'self',
                'cdn.foo.tld',
            ],
            CSP::upgradeInsecureRequests => [],
        ]);
        self::assertSame(
            "default-src 'self'; style-src 'self' cdn.foo.tld; upgrade-insecure-requests;",
            (string) $csp
        );
    }

    public function testRenderWithoutDirectives() : void
    {
        $csp = new CSP();
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No CSP directive has been set');
        $csp->render();
    }

    public function testRender() : void
    {
        $csp = new CSP([
            CSP::defaultSrc => [
                'self',
            ],
            CSP::upgradeInsecureRequests => [],
            CSP::styleSrc => [
                'self',
                'cdn.foo.tld',
            ],
        ]);
        self::assertSame(
            "default-src 'self'; upgrade-insecure-requests; style-src 'self' cdn.foo.tld;",
            $csp->render()
        );
    }

    public function testAddValues() : void
    {
        $csp = new CSP([
            CSP::defaultSrc => [
                'self',
            ],
        ]);
        self::assertSame([
            "'self'",
        ], $csp->getDirective(CSP::defaultSrc));
        self::assertNull($csp->getDirective(CSP::upgradeInsecureRequests));
        $csp->addValues(CSP::upgradeInsecureRequests, []);
        self::assertSame([], $csp->getDirective(CSP::upgradeInsecureRequests));
        $csp->addValues(CSP::defaultSrc, ['cdn.foo.tld']);
        self::assertSame([
            "'self'",
            'cdn.foo.tld',
        ], $csp->getDirective(CSP::defaultSrc));
    }

    public function testSetAndGetDirectives() : void
    {
        $csp = new CSP();
        self::assertSame([], $csp->getDirectives());
        self::assertNull($csp->getDirective('default-src'));
        $csp->setDirective('default-src', ['foo.com']);
        self::assertSame([
            'default-src' => [
                'foo.com',
            ],
        ], $csp->getDirectives());
        self::assertSame(['foo.com'], $csp->getDirective('default-src'));
        $csp->setDirectives([
            'default-src' => [
                'self',
                'cdn.bar.tld',
            ],
        ]);
        self::assertSame([
            'default-src' => [
                "'self'",
                'cdn.bar.tld',
            ],
        ], $csp->getDirectives());
        self::assertSame([
            "'self'",
            'cdn.bar.tld',
        ], $csp->getDirective('default-src'));
    }

    public function testRemoveDirective() : void
    {
        $csp = new CSP([
            CSP::defaultSrc => [
                'self',
            ],
        ]);
        self::assertSame(["'self'"], $csp->getDirective(CSP::defaultSrc));
        $csp->removeDirective(CSP::defaultSrc);
        self::assertNull($csp->getDirective(CSP::defaultSrc));
    }

    protected function getNonceAttrValue(string $attribute) : string
    {
        \preg_match('# nonce="(.*)"#', $attribute, $matches);
        return "'nonce-{$matches[1]}'";
    }

    public function testScriptNonceAttr() : void
    {
        $csp = new CSP();
        self::assertNull($csp->getDirective('script-src'));
        $attribute1 = $csp->getScriptNonceAttr();
        self::assertSame([
            $this->getNonceAttrValue($attribute1),
        ], $csp->getDirective('script-src'));
        $attribute2 = $csp->getScriptNonceAttr();
        $attribute3 = $csp->getScriptNonceAttr();
        self::assertSame([
            $this->getNonceAttrValue($attribute1),
            $this->getNonceAttrValue($attribute2),
            $this->getNonceAttrValue($attribute3),
        ], $csp->getDirective('script-src'));
    }

    public function testStyleNonceAttr() : void
    {
        $csp = new CSP();
        self::assertNull($csp->getDirective('style-src'));
        $attribute1 = $csp->getStyleNonceAttr();
        self::assertSame([
            $this->getNonceAttrValue($attribute1),
        ], $csp->getDirective('style-src'));
        $attribute2 = $csp->getStyleNonceAttr();
        $attribute3 = $csp->getStyleNonceAttr();
        self::assertSame([
            $this->getNonceAttrValue($attribute1),
            $this->getNonceAttrValue($attribute2),
            $this->getNonceAttrValue($attribute3),
        ], $csp->getDirective('style-src'));
    }

    public function testGetStyleContents() : void
    {
        $html = \file_get_contents(__DIR__ . '/files/csp.php');
        $contents = CSP::getStyleContents($html); // @phpstan-ignore-line
        $style1 = '
        body {
            background: cyan;
        }
    ';
        $style2 = '
        body {
            color: blue;
        }
        h1 {color: yellow}
    ';
        self::assertSame([
            $style1,
            $style2,
        ], $contents);
    }

    public function testGetStyleHashes() : void
    {
        $html = \file_get_contents(__DIR__ . '/files/csp.php');
        $hashes = CSP::getStyleHashes($html); // @phpstan-ignore-line
        self::assertSame([
            'sha256-CvbCUHrSwRhSRk6O3h7eTuSY9r3oKFudXNGTM/oLBI8=',
            'sha256-+M1zyEhFl1+uox+Dkoy3mxiTpfC6LJeyioQQevUbcDk=',
        ], $hashes);
    }

    public function testGetScriptContents() : void
    {
        $html = \file_get_contents(__DIR__ . '/files/csp.php');
        $contents = CSP::getScriptContents($html); // @phpstan-ignore-line
        $script1 = "
    console.log('Hello!');
";
        $script2 = "
    console.log('Bye.');
    // it is a comment
";
        self::assertSame([
            $script1,
            $script2,
        ], $contents);
    }

    public function testGetScriptHashes() : void
    {
        $html = \file_get_contents(__DIR__ . '/files/csp.php');
        $hashes = CSP::getScriptHashes($html); // @phpstan-ignore-line
        self::assertSame([
            'sha256-IfEVrz7Me6SW7O7OHy04/VaUhErMLxjWHdJd8MYN5b0=',
            'sha256-0TppQmjw9at2nEl3givShY5l6nABmQ84qrh1dRgvMJ0=',
        ], $hashes);
    }

    public function testMakeHashes() : void
    {
        self::assertSame(
            [
                'sha256-LCa0a2j/xo/5m0U8HTBBNBNCLXBkg7+g+YpeiGJm564=',
                'sha256-/N4rLtula/QIYB+3If6bXDONEO5CnqBPrlURto+/j7k=',
                'sha256-uqWglk0zIPvAxqkiFARTyFE+okq4/QV3A0gEqWckgJY=',
            ],
            CSP::makeHashes(['foo', 'bar', 'baz'])
        );
        self::assertSame(
            [
                'sha384-mMEf/f3VQGdrGhN8saIrKnA1DJpEFx1rEYDGvly7LuP3nVMsih3Z7y6OCOdSo7q7',
            ],
            CSP::makeHashes(['foo'], 'sha384')
        );
    }

    public function testMakeHash() : void
    {
        self::assertSame(
            'sha256-LCa0a2j/xo/5m0U8HTBBNBNCLXBkg7+g+YpeiGJm564=',
            CSP::makeHash('sha256', 'foo')
        );
        self::assertSame(
            'sha384-mMEf/f3VQGdrGhN8saIrKnA1DJpEFx1rEYDGvly7LuP3nVMsih3Z7y6OCOdSo7q7',
            CSP::makeHash('sha384', 'foo')
        );
        self::assertSame(
            'sha512-9/u6bgY2+JDlb7vzKD5STG+jIErimDgtYkdB0NxmODJuKCxBvl5CVNiCB3LFUYosWowMf37aGVlKfrU5RT4e1w==',
            CSP::makeHash('sha512', 'foo')
        );
    }

    public function testSanitizeValue() : void
    {
        $csp = new class() extends CSP {
            public function sanitizeValue(string $value) : string
            {
                return parent::sanitizeValue($value);
            }
        };
        self::assertSame("'none'", $csp->sanitizeValue('none'));
        self::assertSame("'self'", $csp->sanitizeValue('self'));
        self::assertSame("'strict-dynamic'", $csp->sanitizeValue('strict-dynamic'));
        self::assertSame("'unsafe-eval'", $csp->sanitizeValue('unsafe-eval'));
        self::assertSame("'unsafe-hashes'", $csp->sanitizeValue('unsafe-hashes'));
        self::assertSame("'unsafe-inline'", $csp->sanitizeValue('unsafe-inline'));
        self::assertSame("'nonce-foo'", $csp->sanitizeValue('nonce-foo'));
        self::assertSame("'sha256-foo'", $csp->sanitizeValue('sha256-foo'));
        self::assertSame("'sha384-foo'", $csp->sanitizeValue('sha384-foo'));
        self::assertSame("'sha512-foo'", $csp->sanitizeValue('sha512-foo'));
        self::assertSame('foo', $csp->sanitizeValue(' foo '));
    }
}
