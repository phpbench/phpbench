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
        foreach ($iterator as $name => $parameters) {
            $result[$name] = $parameters->getArrayCopy();
        }

        $this->assertEquals($expected, $result);
    }

    public function provideIterate()
    {
        yield 'named sets' => [
            [
                [
                    'opt false' => ['optimized' => false],
                    'opt true' => ['optimized' => true],
                ],
                [
                    '4 foos' => ['nb_foos' => 4],
                    '5 foos' => ['nb_foos' => 5],
                ],
            ],
            [
                'opt false,4 foos' => [
                    'optimized' => false,
                    'nb_foos' => 4,
                ],
                'opt true,4 foos' => [
                    'optimized' => true,
                    'nb_foos' => 4,
                ],
                'opt false,5 foos' => [
                    'optimized' => false,
                    'nb_foos' => 5,
                ],
                'opt true,5 foos' => [
                    'optimized' => true,
                    'nb_foos' => 5,
                ],
            ],
        ];

        yield 'cartesian' => [
            [
                'opts' => [
                    ['optimized' => false],
                    ['optimized' => true],
                ],
                [
                    ['nb_foos' => 4],
                    ['nb_foos' => 5],
                ],
            ],
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
        ];
    }
}
