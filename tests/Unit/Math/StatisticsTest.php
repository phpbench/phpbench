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

namespace PhpBench\Tests\Unit\Math;

use Generator;
use InvalidArgumentException;
use PhpBench\Math\Statistics;
use PHPUnit\Framework\TestCase;

class StatisticsTest extends TestCase
{
    /**
     * It should return the standard deviation.
     */
    public function testStdev()
    {
        $this->assertEquals(1.4142, round(Statistics::stdev([1, 2, 3, 4, 5]), 4));
        $this->assertEquals(17.2116, round(Statistics::stdev([13, 23, 12, 44, 55]), 4));
        $this->assertEquals(0, round(Statistics::stdev([1]), 4));
        $this->assertEquals(0, round(Statistics::stdev([1, 1, 1]), 4));
        $this->assertEquals(2.47, round(Statistics::stdev([2, 6, 4, 1, 7, 3, 6, 1, 7, 1, 6, 5, 1, 1], true), 2));
    }

    /**
     * It should return the average.
     */
    public function testMean()
    {
        $expected = 33 / 7;
        $this->assertEquals($expected, Statistics::mean([2, 2, 2, 2, 2, 20, 3]));
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
        return [
            [
                2, 3, 5,
                true,
                [2, 2.25, 2.5, 2.75, 3],
            ],
            [
                2, 10, 5,
                true,
                [2, 4, 6, 8, 10],
            ],
            [
                2, 10, 5,
                false,
                [
                    2,
                    3.6000000000000001,
                    5.2000000000000002,
                    6.8000000000000007,
                    8.4000000000000004,
                ],
            ],
        ];
    }

    /**
     * It should throw an exception if the linspace min and max are the same number.
     *
     */
    public function testLinspaceMinMaxSame()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Min and max cannot be the same number: 4');
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
        return [
            [
                [
                    10, 20, 15, 5,
                ],
                10,
                'silverman',
                13.33,
            ],
            [
                [
                    10, 20,
                ],
                10,
                'silverman',
                15.56,
            ],
            [
                // custom bandwidth, multimodal
                [
                    10, 20, 15, 5,
                ],
                10,
                0.2,
                12.5,
            ],
            [
                // only one element
                [
                    10,
                ],
                10,
                0.1,
                10,
            ],
            [
                // min and max the same
                [
                    10, 10, 10,
                ],
                10,
                0.1,
                10,
            ],
        ];
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
        return [
            [
                [10, 10, 2, 2],
                10,
                null, null,
                [
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
                ],
            ],
            [
                [1, 10, 2, 2, 2, 3, 2, 4],
                9,
                null, null,
                [
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
                ],
            ],
            [
                [
                    -3,
                    -2,
                    -1,
                    0,
                    1,
                    2,
                    3,
                ],
                10,
                -3,
                3,
                [
                    -3 => 1,
                    '-2.4' => 1,
                    '-1.8' => 0,
                    '-1.2' => 1,
                    '-0.6' => 1,
                    '2.2204460492503E-16' => 0,
                    '0.6' => 1,
                    '1.2' => 0,
                    '1.8' => 1,
                    '2.4' => 1,
                    3 => 0,
                ],
            ],
        ];
    }

    /**
     * @dataProvider providePercentageDifference
     */
    public function testPercentageDifference(float $value1, float $value2, float $expected): void
    {
        $result = Statistics::percentageDifference($value1, $value2);

        if ((string)$expected == 'NAN') {
            self::assertEquals('NAN', (string)$result);

            return;
        }
        self::assertEquals(round($expected, 2), $result);
    }

    /**
     * @return Generator<mixed>
     */
    public function providePercentageDifference(): Generator
    {
        yield 'zero' => [
            0,
            0,
            0
        ];

        yield 'zero and one' => [
            0,
            1,
            INF,
        ];

        yield 'one and zero' => [
            1,
            0,
            -100
        ];

        yield 'one and two' => [
            1,
            2,
            100
        ];

        yield 'one and three' => [
            1,
            3,
            200
        ];

        yield 'one and zero point five' => [
            1,
            0.5,
            -50
        ];
    }
}
