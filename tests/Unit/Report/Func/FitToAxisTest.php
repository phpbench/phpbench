<?php

namespace PhpBench\Tests\Unit\Report\Func;

use Generator;
use PHPUnit\Framework\TestCase;

use function PhpBench\Report\Func\fit_to_axis;

class FitToAxisTest extends TestCase
{
    /**
     * @dataProvider provideFitToAxis
     */
    public function testFitToAxis(array $keys, array $rows, array $expectedSeriesSets): void
    {
        self::assertEquals($expectedSeriesSets, fit_to_axis($keys, $rows));
    }

    public static function provideFitToAxis(): Generator
    {
        yield 'empty' => [[], [], []];

        yield 'keys only' => [
            [
                'one', 'two',
            ],
            [],
            []
        ];

        yield 'rows only' => [
            [
            ],
            [
                [],
            ],
            [
                [],
            ]
        ];

        yield 'rows only 2' => [
            [
            ],
            [
                [1],
                [2],
            ],
            [
                [],
                [],
            ]
        ];

        yield 'less keys than rows' => [
            [
                0
            ],
            [
                [1],
                [2],
            ],
            [
                [1],
                [2],
            ]
        ];

        yield 'one not matching' => [
            [
                'not'
            ],
            [
                [1],
                [2],
            ],
            [
                [0,],
                [0,],
            ]
        ];

        yield 'two not matching' => [
            [
                'not',
                'matching'
            ],
            [
                [1],
                [2],
            ],
            [
                [0, 0],
                [0, 0],
            ]
        ];

        yield [
            [
                'one',
                'two',
                'two.5',
                'three'
            ],
            [
                [
                    'one' => 1,
                    'two' => 2,
                    'three' => 3
                ],
                [
                    'two' => 2,
                ]
            ],
            [
                [1, 2, 0, 3],
                [0, 2, 0, 0]
            ]
        ];
    }
}
