<?php
/*
 * This file is part of Aplus Framework HTTP Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\HTTP\Debug;

use Framework\HTTP\Debug\HTTPCollection;
use PHPUnit\Framework\TestCase;

final class HTTPCollectionTest extends TestCase
{
    protected HTTPCollection $collection;

    protected function setUp() : void
    {
        $this->collection = new HTTPCollection('HTTP');
    }

    public function testIcon() : void
    {
        self::assertStringStartsWith('<svg ', $this->collection->getIcon());
    }
}
