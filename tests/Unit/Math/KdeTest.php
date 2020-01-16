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

namespace PhpBench\Tests\Tests\Unit\Unit\Math;

use InvalidArgumentException;
use OutOfBoundsException;
use PhpBench\Math\Kde;
use PhpBench\Math\Statistics;
use PHPUnit\Framework\TestCase;

class KdeTest extends TestCase
{
    /**
     * It should evaluate a kernel distribution estimate over a given space.
     *
     * @dataProvider provideEvaluate
     */
    public function testEvaluate($dataSet, $space, $bwMethod, $expected)
    {
        $kde = new Kde($dataSet, $bwMethod);
        $result = $kde->evaluate($space);

        // round result
        $result = array_map(function ($v) {
            return round($v, 8);
        }, $result);

        $this->assertEquals($expected, $result);
    }

    public function provideEvaluate()
    {
        return [
            [
                [
                    10, 20, 15, 5,
                ],
                Statistics::linspace(0, 9, 10),
                'silverman',
                [
                    0.01537595, 0.0190706, 0.02299592, 0.02700068, 0.03092369, 0.0346125, 0.03794007, 0.0408159, 0.04318983, 0.04504829,
                ],
            ],
            [
                [
                    10, 20, 15, 5,
                ],
                Statistics::linspace(0, 3, 4),
                'scott',
                [
                    0.01480612,  0.01869787,  0.02286675,  0.02713209,
                ],
            ],
            [
                [
                    10, 20, 15, 5,
                ],
                Statistics::linspace(0, 3, 4),
                'silverman',
                [
                    0.01537595, 0.0190706, 0.02299592, 0.02700068,
                ],
            ],
        ];
    }

    /**
     * It should throw an exception if an invalid bandwidth method is given.
     *
     */
    public function testInvalidBandwidth()
    {
        $this->expectException(InvalidArgumentException::class);
        new Kde([1, 2], 'foo');
    }

    /**
     * It should throw an exception if the data set has zero elements.
     *
     */
    public function testNoElements()
    {
        $this->expectException(OutOfBoundsException::class);
        new Kde([]);
    }

    /**
     * It should throw an exception if the data set has only a single element.
     *
     */
    public function testOneElement()
    {
        $this->expectException(OutOfBoundsException::class);
        new Kde([1]);
    }
}
