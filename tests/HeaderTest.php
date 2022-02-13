<?php
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\HTTP;

use Framework\HTTP\Header;
use PHPUnit\Framework\TestCase;

final class HeaderTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testNames() : void
    {
        self::assertSame('Host', Header::getName('host'));
        Header::setName('HOsT');
        self::assertSame('HOsT', Header::getName('host'));
    }

    public function testGetMultilines() : void
    {
        foreach (Header::getMultilines() as $name) {
            self::assertSame(\strtolower($name), $name);
        }
    }

    public function testIsMultiline() : void
    {
        self::assertTrue(Header::isMultiline('Date'));
        self::assertTrue(Header::isMultiline('SET-COOKIE'));
        self::assertFalse(Header::isMultiline('Etag'));
    }

    public function testConstants() : void
    {
        $reflection = new \ReflectionClass(Header::class);
        foreach ($reflection->getConstants() as $name => $value) {
            self::assertSame(\strtoupper($name), $name);
            self::assertSame(Header::getName($value), $value);
            $name = \strtr(\strtolower($name), ['_' => '-']);
            $value = \strtolower($value);
            self::assertSame($name, $value);
        }
    }
}
