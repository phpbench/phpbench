<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report\Tool;

use PhpBench\Report\Tool\Sort;

class SortTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should sort and preserve order for unchanging comparisons.
     */
    public function testSort()
    {
        $array = array(
            array('col1' => 20, 'col2' => 20),
            array('col1' => 20, 'col2' => 10),
            array('col1' => 10, 'col2' => 50),
            array('col1' => 10, 'col2' => 10),
            array('col1' => 10, 'col2' => 20),
        );

        $expected = array(
            array('col1' => 10, 'col2' => 50),
            array('col1' => 10, 'col2' => 10),
            array('col1' => 10, 'col2' => 20),
            array('col1' => 20, 'col2' => 20),
            array('col1' => 20, 'col2' => 10),
        );

        Sort::mergesort($array, function ($row1, $row2) {
            return strcmp($row1['col1'], $row2['col1']);
        });

        $this->assertEquals($expected, $array);
    }

    /**
     * It should sort a flat array using given column names.
     *
     * @dataProvider provideSortRows
     */
    public function testSortRows($sorting, $expected)
    {
        $array = array(
            array('col1' => 20, 'col2' => 20),
            array('col1' => 20, 'col2' => 10),
            array('col1' => 10, 'col2' => 50),
            array('col1' => 10, 'col2' => 10),
            array('col1' => 10, 'col2' => 20),
        );

        Sort::sortRows($array, $sorting);

        $this->assertEquals($expected, $array);
    }

    public function provideSortRows()
    {
        return array(
            array(
                array('col1', 'col2'),
                array(
                    array('col1' => 10, 'col2' => 10),
                    array('col1' => 10, 'col2' => 20),
                    array('col1' => 10, 'col2' => 50),
                    array('col1' => 20, 'col2' => 10),
                    array('col1' => 20, 'col2' => 20),
                ),
            ),
            array(
                array('col1'),
                array(
                    array('col1' => 10, 'col2' => 50),
                    array('col1' => 10, 'col2' => 10),
                    array('col1' => 10, 'col2' => 20),
                    array('col1' => 20, 'col2' => 20),
                    array('col1' => 20, 'col2' => 10),
                ),
            ),
            array(
                array('col1' => 'desc', 'col2' => 'asc'),
                array(
                    array('col1' => 20, 'col2' => 10),
                    array('col1' => 20, 'col2' => 20),
                    array('col1' => 10, 'col2' => 10),
                    array('col1' => 10, 'col2' => 20),
                    array('col1' => 10, 'col2' => 50),
                ),
            ),
        );
    }
}
