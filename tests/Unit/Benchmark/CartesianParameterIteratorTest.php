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

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Benchmark\CartesianParameterIterator;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\ParameterSetsCollection;
use PhpBench\Tests\TestCase;

class CartesianParameterIteratorTest extends TestCase
{
    /**
     * It should generate the cartestian product of all sets for each iteration.
     *
     * @dataProvider provideIterate
     */
    public function testIterate($parameterSets, $expected): void
    {
        $iterator = new CartesianParameterIterator(ParameterSetsCollection::fromUnserializedParameterSetsCollection($parameterSets));
        $result = [];

        foreach ($iterator as $parameters) {
            assert($parameters instanceof ParameterSet);
            $result[$parameters->getName()] = $parameters->toUnserializedParameters();
        }

        $this->assertEquals($expected, $result);
    }

    public static function provideIterate()
    {
        yield '0 x 0' => [
            [
            ],
            [
            ],
        ];

        yield '1 x 0' => [
            [
                [
                ],
            ],
            [
                '' => [
                ],
            ],
        ];

        yield '1 x 1' => [
            [
                [
                    ['one' => 1],
                ],
            ],
            [
                '0' => [
                    'one' => 1,
                ],
            ],
        ];

        yield '2 x 2' => [
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
            [
                '0,0' => [
                    'optimized' => false,
                    'nb_foos' => 4,
                ],
                '1,0' => [
                    'optimized' => true,
                    'nb_foos' => 4,
                ],
                '0,1' => [
                    'optimized' => false,
                    'nb_foos' => 5,
                ],
                '1,1' => [
                    'optimized' => true,
                    'nb_foos' => 5,
                ],
            ],
        ];

        yield '3 x 2' => [
            [
                [
                    ['one' => 1],
                    ['two' => 2],
                ],
                [
                    ['three' => 3],
                    ['four' => 4],
                ],
                [
                    ['five' => 5],
                    ['six' => 6],
                ],
            ],
            [
                '0,0,0' => [
                    'one' => 1,
                    'three' => 3,
                    'five' => 5,
                ],
                '1,0,0' => [
                    'three' => 3,
                    'five' => 5,
                    'two' => 2,
                ],
                '0,1,0' => [
                    'one' => 1,
                    'four' => 4,
                    'five' => 5,
                ],
                '0,0,1' => [
                    'one' => 1,
                    'three' => 3,
                    'six' => 6,
                ],
                '1,1,0' => [
                    'two' => 2,
                    'four' => 4,
                    'five' => 5,
                ],
                '1,0,1' => [
                    'two' => 2,
                    'three' => 3,
                    'six' => 6,
                ],
                '0,1,1' => [
                    'one' => 1,
                    'four' => 4,
                    'six' => 6,
                ],
                '1,1,1' => [
                    'two' => 2,
                    'four' => 4,
                    'six' => 6,
                ],
            ],
        ];

        yield 'named' => [
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

        yield 'uneven first set' => [
            [
                [
                    [
                        'one' => 1,
                        'two' => 2,
                    ],
                ],
                [
                    [
                        'three' => 3,
                    ],
                ],
            ],
            [
                '0,0' => [
                    'one' => 1,
                    'two' => 2,
                    'three' => 3,
                ],
            ],
        ];

        yield 'uneven second set' => [
            [
                [
                    [
                        'one' => 1,
                    ],
                ],
                [
                    [
                        'two' => 2,
                        'three' => 3,
                    ],
                ],
            ],
            [
                '0,0' => [
                    'one' => 1,
                    'two' => 2,
                    'three' => 3,
                ],
            ],
        ];
    }
}
