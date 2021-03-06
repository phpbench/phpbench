<?php

namespace PhpBench\Tests\Unit\Data\Func;

use Generator;
use PhpBench\Data\DataFrame;
use PHPUnit\Framework\TestCase;

class PartitionTest extends TestCase
{
    /**
     * @dataProvider providePartition
     */
    public function testPartition(array $records, array $columns, array $expected): void
    {
        self::assertEquals(
            $expected,
            DataFrame::fromRecords($records)->partition($columns)->toArray()
        );
    }

    public function providePartition(): Generator
    {
        yield [
            [
            ],
            [],
            [
            ]
        ];

        yield [
            [
            ],
            ['a'],
            [
            ]
        ];

        yield [
            [
                ['a' => 'two', 'b' => 1],
                ['a' => 'two', 'b' => 2],
            ],
            ['a'],
            [
                'two' => [
                    ['a' => 'two', 'b' => 1],
                    ['a' => 'two','b' => 2],
                ]
            ]
        ];

        yield [
            [
                ['a' => 'two', 'b' => 1],
                ['a' => 'two', 'b' => 2],
                ['a' => 'three', 'b' => 1],
            ],
            ['a'],
            [
                'two' => [
                    ['a' => 'two', 'b' => 1],
                    ['a' => 'two', 'b' => 2],
                ],
                'three' => [
                    ['a' => 'three', 'b' => 1],
                ]
            ]
        ];

        yield [
            [
                ['a' => 'two', 'b' => 1],
                ['a' => 'two', 'b' => 2],
                ['a' => 'three', 'b' => 1],
                ['a' => 'three', 'b' => 1],
            ],
            ['a', 'b'],
            [
                'two-1' => [
                    ['a' => 'two', 'b' => 1],
                ],
                'two-2' => [
                    ['a' => 'two', 'b' => 2],
                ],
                'three-1' => [
                    ['a' => 'three', 'b' => 1],
                    ['a' => 'three', 'b' => 1],
                ]
            ]
        ];

        yield [
            [
                ['a' => 'two', 'b' => 1, 'c' => 1],
                ['a' => 'two', 'b' => 2, 'c' => 2],
                ['a' => 'three', 'b' => 1, 'c' => 3],
                ['a' => 'three', 'b' => 1, 'c' => 4],
            ],
            ['a', 'b'],
            [
                'two-1' => [
                    ['a' => 'two', 'b' => 1, 'c' => 1],
                ],
                'two-2' => [
                    ['a' => 'two', 'b' => 2, 'c' => 2],
                ],
                'three-1' => [
                    ['a' => 'three', 'b' => 1, 'c' => 3],
                    ['a' => 'three', 'b' => 1, 'c' => 4],
                ]
            ]
        ];
    }
}
