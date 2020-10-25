<?php

namespace PhpBench\Tests\Unit\Assertion\MessageFormatter;

use Generator;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\MessageFormatter\NodeMessageFormatter;
use PhpBench\Tests\TestCase;

class NodeMessageFormatterTest extends TestCase
{
    /**
     * @dataProvider provideTimeValue
     * @dataProvider provideThroughputValue
     * @dataProvider provideComparison
     * @dataProvider provideMemoryValue
     */
    public function testFormat(Node $node, array $args, string $expected): void
    {
        self::assertEquals($expected, (new NodeMessageFormatter($args))->format($node));
    }
        
    /**
     * @return Generator<mixed>
     */
    public function provideTimeValue(): Generator
    {
        yield [
                new TimeValue(10, 'microseconds'),
                [],
                '10μs',
            ];

        yield [
                new TimeValue(10, 'milliseconds'),
                [],
                '10ms',
            ];

        yield [
                new TimeValue(10, 'seconds'),
                [],
                '10s',
            ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideThroughputValue(): Generator
    {
        yield [
                new ThroughputValue(10, 'second'),
                [],
                '10 ops/s'
            ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideComparison(): Generator
    {
        yield 'normalise property access unit 1' => [
                new Comparison(
                    new PropertyAccess(['foo', 'bar']),
                    '>',
                    new TimeValue(10, 'seconds'),
                    new TimeValue(5, 'seconds')
                ),
                [
                    'foo' => [
                        'bar' => 1E7
                    ]
                ],
                '10s > 10s ± 5s',
            ];

        yield 'normalise property access unit 2' => [
                new Comparison(
                    new TimeValue(10, 'seconds'),
                    '>',
                    new PropertyAccess(['foo', 'bar']),
                    new TimeValue(5, 'seconds')
                ),
                [
                    'foo' => [
                        'bar' => 1E7
                    ]
                ],
                '10s > 10s ± 5s',
            ];

        yield 'normalise property access unit 3' => [
                new Comparison(
                    new TimeValue(10, 'seconds'),
                    '>',
                    new TimeValue(5, 'seconds'),
                    new PropertyAccess(['foo', 'bar'])
                ),
                [
                    'foo' => [
                        'bar' => 5E6
                    ]
                ],
                '10s > 5s ± 5s',
            ];

        yield 'normalise property access unit 4' => [
                new Comparison(
                    new PropertyAccess(['foo', 'bar']),
                    '>',
                    new ThroughputValue(5, 'second'),
                    new TimeValue(10, 'seconds')
                ),
                [
                    'foo' => [
                        'bar' => 5E6
                    ]
                ],
                '0.2 ops/s > 5 ops/s ± 10s',
            ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideMemoryValue(): Generator
    {
        yield [
            new MemoryValue(10, 'bytes'),
            [],
            '10 bytes',
        ];

        yield [
            new MemoryValue(10.3, 'megabytes'),
            [],
            '10.300 megabytes',
        ];

        yield 'normalise property access 1' => [
                new Comparison(
                    new PropertyAccess(['foo', 'bar']),
                    '>',
                    new MemoryValue(10, 'megabytes')
                ),
                [
                    'foo' => [
                        'bar' => 5E6
                    ]
                ],
                '5 megabytes > 10 megabytes ± 0',
            ];

        yield 'normalise property access 2' => [
                new Comparison(
                    new PropertyAccess(['foo', 'bar']),
                    '>',
                    new MemoryValue(10, 'megabytes'),
                    new MemoryValue(1, 'megabytes')
                ),
                [
                    'foo' => [
                        'bar' => 5E6
                    ]
                ],
                '5 megabytes > 10 megabytes ± 1 megabytes',
            ];
    }
}
