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

    /**
     * It should generate a linear space.
     *
     * @dataProvider provideLinearSpace
     */
    public function testLinearSpace($min, $max, $steps, $endpoint, $expected)
    {
        $result = Statistics::linspace($min, $max, $steps, $endpoint);
        $this->assertEquals($expected, $result);
    }

    public function provideLinearSpace()
    {
        return array(
            array(
                2, 3, 5,
                true,
                array(2, 2.25, 2.5, 2.75, 3),
            ),
            array(
                2, 10, 5,
                true,
                array(2, 4, 6, 8, 10),
            ),
            array(
                2, 10, 5,
                false,
                array(
                    2,
                    3.6000000000000001,
                    5.2000000000000002,
                    6.8000000000000007,
                    8.4000000000000004,
                ),
            ),
        );
    }

    /**
     * It should throw an exception if the linspace min and max are the same number.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Min and max cannot be the same number: 4
     */
    public function testLinspaceMinMaxSame()
    {
        Statistics::linspace(4, 4, 10);
    }

    /**
     * @dataProvider provideKdeMode
     */
    public function testKdeMode($population, $space, $bandwidth, $expected)
    {
        $result = Statistics::kdeMode($population, $space, $bandwidth);
        $this->assertEquals($expected, round($result, 2));
    }

    public function provideKdeMode()
    {
        return array(
            array(
                array(
                    10, 20, 15, 5,
                ),
                10,
                'silverman',
                13.33,
            ),
            array(
                array(
                    10, 20,
                ),
                10,
                'silverman',
                15.56,
            ),
            array(
                // custom bandwidth, multimodal
                array(
                    10, 20, 15, 5,
                ),
                10,
                0.2,
                12.5,
            ),
            array(
                // only one element
                array(
                    10,
                ),
                10,
                0.1,
                10,
            ),
            array(
                // min and max the same
                array(
                    10, 10, 10,
                ),
                10,
                0.1,
                10,
            ),
        );
    }
}
