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

namespace PhpBench\Tabular\Tests\Unit;

use PhpBench\Report\Generator\Table\Sort;
use PHPUnit\Framework\TestCase;

class SortTest extends TestCase
{
    /**
     * It should sort and preserve order for unchanging comparisons.
     */
    public function testSort()
    {
        $array = [
            ['col1' => 20, 'col2' => 20],
            ['col1' => 20, 'col2' => 10],
            ['col1' => 10, 'col2' => 50],
            ['col1' => 10, 'col2' => 10],
            ['col1' => 10, 'col2' => 20],
        ];

        $expected = [
            ['col1' => 10, 'col2' => 50],
            ['col1' => 10, 'col2' => 10],
            ['col1' => 10, 'col2' => 20],
            ['col1' => 20, 'col2' => 20],
            ['col1' => 20, 'col2' => 10],
        ];

        Sort::mergeSort($array, function ($row1, $row2) {
            return strcmp($row1['col1'], $row2['col1']);
        });

        $this->assertEquals($expected, $array);
    }
}
