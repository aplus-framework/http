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

use Framework\HTTP\Status;
use PHPUnit\Framework\TestCase;

final class StatusTest extends TestCase
{
    public function testGetReason() : void
    {
        self::assertSame('OK', Status::getReason(200));
        self::assertSame('Not Found', Status::getReason(404));
        self::assertSame('Foo Bar', Status::getReason(567, 'Foo Bar'));
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unknown status code must have a default reason: 567');
        Status::getReason(567);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetStatus() : void
    {
        self::assertSame('OK', Status::getReason(200));
        Status::setStatus(200, 'Foo');
        self::assertSame('Foo', Status::getReason(200));
    }

    public function testValidate() : void
    {
        self::assertSame(100, Status::validate(100));
        self::assertSame(599, Status::validate(599));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid response status code: 600');
        Status::validate(600);
    }

    public function testConstants() : void
    {
        $reflection = new \ReflectionClass(Status::class);
        foreach ($reflection->getConstants() as $name => $value) {
            self::assertIsInt($value);
            self::assertSame(\strtoupper($name), $name);
            $reason = Status::getReason($value);
            $reason = \strtolower($reason);
            $name = \strtr(\strtolower($name), ['_' => ' ']);
            if ($name === 'im a teapot') {
                $name = "i'm a teapot";
            } elseif ($name === 'non authoritative information') {
                $name = 'non-authoritative information';
            } elseif ($name === 'multi status') {
                $name = 'multi-status';
            }
            self::assertSame($name, $reason);
        }
    }
}
