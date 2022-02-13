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

use Framework\HTTP\RequestHeader;
use PHPUnit\Framework\TestCase;

final class RequestHeaderTest extends TestCase
{
    public function testParseInput() : void
    {
        self::assertSame([
            'Host' => 'localhost',
            'Content-Type' => 'text/Foo',
        ], RequestHeader::parseInput([
            'Host' => 'Foo',
            'HTTP_HOST' => 'localhost',
            'HTTP_CONTENT_TYPE' => 'text/Foo',
            'HTTPS' => 'on',
        ]));
    }

    public function testConstants() : void
    {
        $reflection = new \ReflectionClass(RequestHeader::class);
        foreach ($reflection->getConstants() as $name => $value) {
            self::assertSame(\strtoupper($name), $name);
            self::assertSame(RequestHeader::getName($value), $value);
            $name = \strtr(\strtolower($name), ['_' => '-']);
            $value = \strtolower($value);
            self::assertSame($name, $value);
        }
    }
}
