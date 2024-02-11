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

use PHPUnit\Framework\TestCase;

final class HeaderTraitTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testNames() : void
    {
        self::assertSame('Host', HeaderTraitMock::getName('host'));
        HeaderTraitMock::setName('HOsT');
        self::assertSame('HOsT', HeaderTraitMock::getName('host'));
    }

    public function testGetMultilines() : void
    {
        foreach (HeaderTraitMock::getMultilines() as $name) {
            self::assertSame(\strtolower($name), $name);
        }
    }

    public function testIsMultiline() : void
    {
        self::assertTrue(HeaderTraitMock::isMultiline('Date'));
        self::assertTrue(HeaderTraitMock::isMultiline('SET-COOKIE'));
        self::assertFalse(HeaderTraitMock::isMultiline('Etag'));
    }

    public function testConstants() : void
    {
        $reflection = new \ReflectionClass(HeaderTraitMock::class);
        foreach ($reflection->getConstants() as $name => $value) {
            self::assertSame(\strtoupper($name), $name);
            self::assertSame(HeaderTraitMock::getName($value), $value);
            $name = \strtr(\strtolower($name), ['_' => '-']);
            $value = \strtolower($value);
            self::assertSame($name, $value);
        }
    }
}
