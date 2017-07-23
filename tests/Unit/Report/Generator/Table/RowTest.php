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

class RowTest extends TestCase
{
    /**
     * It should throw an exception if a non-existing offset is requested.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Column "foo" does not exist, valid columns: "bar"
     */
    public function testGetNotExisting()
    {
        $row = new Row(['bar' => 'boo']);
        $row['foo'];
    }

    /**
     * It should return a given key.
     */
    public function testGet()
    {
        $row = new Row(['bar' => 'boo']);
        $value = $row['bar'];

        $this->assertEquals('boo', $value);
    }

    /**
     * It should merge a given array and return a new instance with the
     * merged data.
     */
    public function testMerge()
    {
        $row = new Row(['bar' => 'bar']);
        $row->setFormatParams(['of' => 'fo']);
        $new = $row->merge(['foo' => 'foo']);

        $this->assertNotSame($row, $new);
        $this->assertEquals(['of' => 'fo'], $new->getFormatParams());
        $this->assertEquals([
            'bar' => 'bar',
            'foo' => 'foo',
        ], $new->getArrayCopy());
    }

    /**
     * It should return the names.
     */
    public function testNames()
    {
        $row = new Row(['one' => 1, 'two' => 2]);
        $this->assertEquals(['one', 'two'], $row->getNames());
    }
}
