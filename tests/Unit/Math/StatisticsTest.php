<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Math;

use PhpBench\Math\Statistics;

class StatisticsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should return the standard deviation.
     */
    public function testStdev()
    {
        $this->assertEquals(1.4142, round(Statistics::stdev(array(1, 2, 3, 4, 5)), 4));
        $this->assertEquals(17.2116, round(Statistics::stdev(array(13, 23, 12, 44, 55)), 4));
        $this->assertEquals(0, round(Statistics::stdev(array(1)), 4));
        $this->assertEquals(0, round(Statistics::stdev(array(1, 1, 1)), 4));
        $this->assertEquals(2.47, round(Statistics::stdev(array(2, 6, 4, 1, 7, 3, 6, 1, 7, 1, 6, 5, 1, 1), true), 2));
    }

    /**
     * It should return the average.
     */
    public function testMean()
    {
        $expected = 33 / 7;
        $this->assertEquals($expected, Statistics::mean(array(2, 2, 2, 2, 2, 20, 3)));
    }
}
