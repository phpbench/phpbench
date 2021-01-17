<?php

namespace PhpBench\Tests\Unit\Assertion\Printer;

use Generator;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\FunctionNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\ToleranceNode;
use PhpBench\Assertion\ExpressionEvaluatorFactory;
use PhpBench\Assertion\ExpressionFunctions;
use PhpBench\Assertion\Printer\NodePrinter;
use PhpBench\Tests\TestCase;
use PhpBench\Util\TimeUnit;

class NodePrinterTest extends TestCase
{
    /**
     * @dataProvider provideTimeValue
     * @dataProvider provideComparison
     * @dataProvider provideMemoryValue
     * @dataProvider provideFunction
     * @dataProvider provideOtherNodes
     */
    public function testFormat(Node $node, array $args, string $expected, array $functions = []): void
    {
        self::assertEquals($expected, (new NodePrinter($args, new TimeUnit(
            TimeUnit::MICROSECONDS,
            TimeUnit::MICROSECONDS,
            TimeUnit::MODE_TIME,
            0
        ), new ExpressionEvaluatorFactory(new ExpressionFunctions($functions))))->format($node));
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
            '10 megabytes',
        ];

        yield [
            new MemoryValue(new IntegerNode(1000), 'bytes', 'kilobytes'),
            [],
            '1 kilobytes',
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideFunction(): Generator
    {
        yield [
            new FunctionNode('foo', [new IntegerNode(10)]),
            [],
            '10',
            [
                'foo' => function (int $foo) {
                    return $foo;
                },
            ]
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideOtherNodes(): Generator
    {
        yield [
            new PropertyAccess(['foo', 'bar']),
            [
                'foo' => [
                    'bar' => 'foo',
                ],
            ],
            'foo',
        ];
        yield [
            new PercentageValue(new IntegerNode(10)),
            [
            ],
            '10%',
        ];
        yield [
            new IntegerNode(10),
            [
            ],
            '10',
        ];
    }
}
