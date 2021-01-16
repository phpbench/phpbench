<?php

namespace PhpBench\Tests\Unit\Assertion\Printer;

use Generator;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\ToleranceNode;
use PhpBench\Assertion\Printer\NodePrinter;
use PhpBench\Tests\TestCase;
use PhpBench\Util\TimeUnit;

class NodePrinterTest extends TestCase
{
    /**
     * @dataProvider provideTimeValue
     * @dataProvider provideComparison
     * @dataProvider provideMemoryValue
     */
    public function testFormat(Node $node, array $args, string $expected): void
    {
        self::assertEquals($expected, (new NodePrinter($args, new TimeUnit(
            TimeUnit::MICROSECONDS,
            TimeUnit::MICROSECONDS,
            TimeUnit::MODE_TIME,
            0
        )))->format($node));
    }
        
    /**
     * @return Generator<mixed>
     */
    public function provideTimeValue(): Generator
    {
        yield [
                new TimeValue(new IntegerNode(10), 'microseconds'),
                [],
                '10μs',
            ];

        yield [
                new TimeValue(new IntegerNode(10), 'milliseconds'),
                [],
                '10ms',
            ];

        yield [
                new TimeValue(new IntegerNode(10), 'seconds'),
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
                new ThroughputValue(new IntegerNode(10), 'second'),
                [],
                '10 ops/s'
            ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideComparison(): Generator
    {
        yield 'property access unit 1' => [
                new Comparison(
                    new TimeValue(new PropertyAccess(['foo', 'bar']), 'seconds'),
                    '>',
                    new TimeValue(new IntegerNode(10), 'seconds'),
                    new ToleranceNode(new TimeValue(new IntegerNode(5), 'seconds'))
                ),
                [
                    'foo' => [
                        'bar' => 10
                    ]
                ],
                '10s > 10s ± 5s',
            ];

        yield 'normalise property access unit 2' => [
                new Comparison(
                    new TimeValue(new IntegerNode(10), 'seconds'),
                    '>',
                    new PropertyAccess(['foo', 'bar']),
                    new ToleranceNode(new TimeValue(new IntegerNode(5), 'seconds'))
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
                    new TimeValue(new IntegerNode(10), 'seconds'),
                    '>',
                    new TimeValue(new IntegerNode(5), 'seconds'),
                    new ToleranceNode(new PropertyAccess(['foo', 'bar']))
                ),
                [
                    'foo' => [
                        'bar' => 5E6
                    ]
                ],
                '10s > 5s ± 5s',
            ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideMemoryValue(): Generator
    {
        yield [
            new MemoryValue(new IntegerNode(10), 'bytes'),
            [],
            '10 bytes',
        ];

        yield [
            new MemoryValue(new FloatNode(10.3), 'megabytes'),
            [],
            '10.300 megabytes',
        ];

        yield 'normalise property access 1' => [
                new Comparison(
                    new PropertyAccess(['foo', 'bar']),
                    '>',
                    new MemoryValue(new IntegerNode(10), 'megabytes')
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
                    new MemoryValue(new IntegerNode(10), 'megabytes'),
                    new ToleranceNode(new MemoryValue(new IntegerNode(1), 'megabytes'))
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
