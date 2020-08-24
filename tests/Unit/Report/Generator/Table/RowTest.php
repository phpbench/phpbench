<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Unit\Report\Generator\Table;

use PhpBench\Report\Generator\Table\Row;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RowTest extends TestCase
{
    /**
     * It should throw an exception if a non-existing offset is requested.
     *
     */
    public function testGetNotExisting(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Column "foo" does not exist');
        $row = Row::fromMap(['bar' => 'boo']);
        $row->getValue('foo');
    }

    /**
     * It should return a given key.
     */
    public function testGet(): void
    {
        $row = Row::fromMap(['bar' => 'boo']);
        $value = $row->getValue('bar');

        $this->assertEquals('boo', $value);
    }

    /**
     * It should merge a given array and return a new instance with the
     * merged data.
     */
    public function testMerge(): void
    {
        $row = Row::fromMap(['bar' => 'bar']);
        $row->setFormatParams(['of' => 'fo']);
        $new = $row->mergeMap(['foo' => 'foo']);

        $this->assertNotSame($row, $new);
        $this->assertEquals(['of' => 'fo'], $new->getFormatParams());
        $this->assertEquals([
            'bar' => 'bar',
            'foo' => 'foo',
        ], $new->toArray());
    }

    /**
     * It should return the names.
     */
    public function testNames(): void
    {
        $row = Row::fromMap(['one' => 1, 'two' => 2]);
        $this->assertEquals(['one', 'two'], $row->getNames());
    }

    public function testCloneDereferencesCells(): void
    {
        $row = Row::fromMap(['one' => 1]);
        $newRow = clone $row;

        self::assertNotSame($row->getCell('one'), $newRow->getCell('one'));
    }
}
