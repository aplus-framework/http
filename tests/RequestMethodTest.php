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

use Framework\HTTP\RequestMethod;
use PHPUnit\Framework\TestCase;

final class RequestMethodTest extends TestCase
{
    public function testValidate() : void
    {
        self::assertSame('GET', RequestMethod::validate('Get'));
        self::assertSame('PATCH', RequestMethod::validate('PatCH'));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request method: Foo');
        RequestMethod::validate('Foo');
    }

    public function testConstants() : void
    {
        $reflection = new \ReflectionClass(RequestMethod::class);
        foreach ($reflection->getConstants() as $name => $value) {
            self::assertSame(\strtoupper($name), $name);
            self::assertSame($name, $value);
            self::assertSame($name, RequestMethod::validate($value));
        }
    }
}
