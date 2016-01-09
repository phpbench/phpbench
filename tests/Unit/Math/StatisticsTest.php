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
     * It should return histogram data.
     *
     * @dataProvider provideHistogram
     */
    public function testHistogram(array $data, $steps, $lower, $upper, array $expected)
    {
        $result = Statistics::histogram($data, $steps, $lower, $upper);
        $this->assertEquals($expected, $result);
    }

    public function provideHistogram()
    {
        return array(
            array(
                array(10, 10, 2, 2),
                10,
                null, null,
                array(
                    2 => 2,
                    '2.8' => 0,
                    '3.6' => 0,
                    '4.4' => 0,
                    '5.2' => 0,
                    '6' => 0,
                    '6.8' => 0,
                    '7.6' => 0,
                    '8.4' => 0,
                    '9.2' => 0,
                    10 => 2,
                ),
            ),
            array(
                array(1, 10, 2, 2, 2, 3, 2, 4),
                9,
                null, null,
                array(
                    1 => 1,
                    2 => 4,
                    3 => 1,
                    4 => 1,
                    5 => 0,
                    6 => 0,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                    10 => 1,
                ),
            )
        );
    }


    /**
     * It should calculate the normal probability density function.
     *
     * @dataProvider providePdfNormal
     */
    public function testPdfNormal($xValue, $mean, $stDev, $expected)
    {
        $result = Statistics::pdfNormal($xValue, $mean, $stDev);
        $this->assertEquals($expected, round($result, 8));
    }

    public function providePdfNormal()
    {
        return array(
            array(
                10,
                5,
                2,
                0.00876415,
            )
        );
    }

    /**
     * It should generate a linear space.
     *
     * @dataProvider provideLinearSpace
     */
    public function testLinearSpace($min, $max, $steps, $expected)
    {
        $result = Statistics::linspace($min, $max, $steps);
        $this->assertEquals($expected, $result);
    }

    public function provideLinearSpace()
    {
        return array(
            array(
                2, 3, 5,
                array(2, 2.25, 2.5, 2.75, 3)
            ),
            array(
                2, 10, 5,
                array(2, 4, 6, 8, 10)
            ),
        );
    }

    /**
     * It should generate a kernel density estimate (kde) using
     * a normal kernel.
     *
     * @dataProvider provideKdeNormal
     */
    public function testKdeNormalMode(array $population, $bandwidth, $expected)
    {
        $result = Statistics::kdeNormalMode($population, $bandwidth);
        $this->assertEquals($expected, $result);
    }

    public function provideKdeNormal()
    {
        return array(
            array(
                array(1.0, 4.0, 3.0, 2.0, 2.0, 3.0, 4.0, 1.0, 0.5),
                0.7549,
                2.05859375 // this does not match up with R
            ),
            array(
                array(4),
                123,
                4
            ),
            array(
                array(10, 10, 10),
                123,
                10
            ),
        );
    }
}
