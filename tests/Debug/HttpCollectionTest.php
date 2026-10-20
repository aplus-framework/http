<?php
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Http\Debug;

use Aplus\Http\Debug\HttpCollection;
use PHPUnit\Framework\TestCase;

final class HttpCollectionTest extends TestCase
{
    protected HttpCollection $collection;

    protected function setUp() : void
    {
        $this->collection = new HttpCollection('HTTP');
    }

    public function testIcon() : void
    {
        self::assertStringStartsWith('<svg ', $this->collection->getIcon());
    }
}
