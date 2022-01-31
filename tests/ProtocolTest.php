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

use Framework\HTTP\Protocol;
use PHPStan\Testing\TestCase;

final class ProtocolTest extends TestCase
{
    public function testValidate() : void
    {
        self::assertSame('HTTP/1.0', Protocol::validate('http/1.0'));
        self::assertSame('HTTP/3', Protocol::validate('Http/3'));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid protocol: Foo');
        Protocol::validate('Foo');
    }

    public function testConstants() : void
    {
        $reflection = new \ReflectionClass(Protocol::class);
        foreach ($reflection->getConstants() as $name => $value) {
            self::assertSame(\strtoupper($name), $name);
            self::assertSame($value, Protocol::validate($value));
        }
    }
}
