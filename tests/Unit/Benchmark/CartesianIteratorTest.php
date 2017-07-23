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

namespace PhpBench\Tests;

use PhpBench\Benchmark\CartesianParameterIterator;
use PHPUnit\Framework\TestCase;

class CartesianIteratorTest extends TestCase
{
    /**
     * It should generate the cartestian product of all sets for each iteration.
     *
     * @dataProvider provideIterate
     */
    public function testIterate($parameterSets, $expected)
    {
        $iterator = new CartesianParameterIterator($parameterSets);
        $result = [];
        foreach ($iterator as $parameters) {
            $result[] = $parameters->getArrayCopy();
        }

        $this->assertEquals($expected, $result);
    }

    public function provideIterate()
    {
        return [
            [
                // parameter sets
                [
                    [
                        ['optimized' => false],
                        ['optimized' => true],
                    ],
                    [
                        ['nb_foos' => 4],
                        ['nb_foos' => 5],
                    ],
                ],
                // expected result
                [
                    [
                        'optimized' => false,
                        'nb_foos' => 4,
                    ],
                    [
                        'optimized' => true,
                        'nb_foos' => 4,
                    ],
                    [
                        'optimized' => false,
                        'nb_foos' => 5,
                    ],
                    [
                        'optimized' => true,
                        'nb_foos' => 5,
                    ],
                ],
            ],
        ];
    }
}
