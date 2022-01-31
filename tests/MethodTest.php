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

use Framework\HTTP\Method;
use PHPUnit\Framework\TestCase;

final class MethodTest extends TestCase
{
    public function testValidate() : void
    {
        self::assertSame('GET', Method::validate('Get'));
        self::assertSame('PATCH', Method::validate('PatCH'));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request method: Foo');
        Method::validate('Foo');
    }

    public function testConstants() : void
    {
        $reflection = new \ReflectionClass(Method::class);
        foreach ($reflection->getConstants() as $name => $value) {
            self::assertSame(\strtoupper($name), $name);
            self::assertSame($name, $value);
            self::assertSame($name, Method::validate($value));
        }
    }
}
