<?php

namespace PhpBench\Tests\Unit\Data\Func;

use Closure;
use Generator;
use PhpBench\Data\DataFrame;
use PhpBench\Data\Row;
use PHPUnit\Framework\TestCase;

class PartitionTest extends TestCase
{
    /**
     * @dataProvider providePartition
     */
    public function testPartition(array $records, Closure $paritioner, array $expected): void
    {
        self::assertEquals(
            $expected,
            DataFrame::fromRecords($records)->partition($paritioner)->toArray()
        );
    }

    public function providePartition(): Generator
    {
        yield [
            [
            ],
            function (): void {
            },
            [
            ]
        ];

        yield [
            [
            ],
            function (Row $data) {
                return $data['a'];
            },
            [
            ]
        ];

        yield [
            [
                ['a' => 'two', 'b' => 1],
            ],
            function (Row $data): void {
            },
            [
                '' => [
                    ['a' => 'two', 'b' => 1],
                ]
            ]
        ];

        yield [
            [
                ['a' => 'two', 'b' => 1],
                ['a' => 'two', 'b' => 2],
            ],
            function (Row $data) {
                return $data['a'];
            },
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
            function (Row $data) {
                return $data['a'];
            },
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
            function (Row $data) {
                return $data['a'].'-'.$data['b'];
            },
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
            function (Row $data) {
                return $data['a'].'-'.$data['b'];
            },
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
