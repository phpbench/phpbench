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

namespace PhpBench\Tests\Unit\Assertion;

use Generator;
use PhpBench\Assertion\ArithmeticNode;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\DisplayAsNode;
use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\FunctionNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\ListNode;
use PhpBench\Assertion\Ast\MemoryUnitNode;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\ParenthesizedExpressionNode;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeUnitNode;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\ToleranceNode;
use PhpBench\Assertion\Exception\SyntaxError;

class ExpressionParserTest extends ExpressionParserTestCase
{
    /**
     * @dataProvider provideValues
     * @dataProvider provideComparison
     * @dataProvider provideAggregateFunction
     * @dataProvider provideValueWithUnit
     * @dataProvider provideExpression
     * @dataProvider provideTolerance
     * @dataProvider provideThroughput
     * @dataProvider provideArithmetic
     * @dataProvider provideList
     *
     * @param array<string,mixed> $config
     */
    public function testParse(string $dsl, Node $expected, array $config = []): void
    {
        $parsed = $this->parse($dsl, $config);
        $this->assertEquals($expected, $parsed);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideValues(): Generator
    {
        yield [
            '123',
            new IntegerNode(123),
        ];

        yield [
            '123.12',
            new FloatNode(123.12),
        ];

        yield [
            'this.foobar',
            new PropertyAccess(['this', 'foobar']),
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideComparison(): Generator
    {
        yield 'comp 1' => [
            'this.foobar < 100',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '<',
                new IntegerNode(100)
            )
        ];

        yield 'less than equal' => [
            'this.foobar <= 100',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '<=',
                new IntegerNode(100)
            )
        ];

        yield 'equal' => [
            'this.foobar = 100',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '=',
                new IntegerNode(100)
            )
        ];

        yield 'greater than' => [
            'this.foobar > 100',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '>',
                new IntegerNode(100)
            )
        ];

        yield 'greater than equal' => [
            'this.foobar >= 100',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '>=',
                new IntegerNode(100)
            )
        ];

        yield 'seconds' => [
            '10 seconds < 10 seconds +/- 10 seconds',
            new Comparison(
                new TimeValue(new IntegerNode(10), 'seconds'),
                '<',
                new TimeValue(new IntegerNode(10), 'seconds'),
                new ToleranceNode(new TimeValue(new IntegerNode(10), 'seconds'))
            ),
            [
                'timeUnits' => ['seconds'],
            ]
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideValueWithUnit(): Generator
    {
        yield '100 milliseconds' => [
            '100 milliseconds',
            new TimeValue(new IntegerNode(100), 'milliseconds'),
            [
                'timeUnits' => ['milliseconds']
            ]
        ];

        yield '10.2 milliseconds' => [
            '10.2 milliseconds',
            new TimeValue(new FloatNode(10.2), 'milliseconds'),
            [
                'timeUnits' => ['milliseconds']
            ]
        ];

        yield [
            '100 bytes',
            new MemoryValue(new IntegerNode(100), 'bytes'),
            [
                'memoryUnits' => ['bytes']
            ]
        ];

        yield [
            '100 bytes as megabytes',
            new DisplayAsNode(
                new MemoryValue(new IntegerNode(100), 'bytes'),
                new MemoryUnitNode('megabytes')
            ),
            [
                'memoryUnits' => ['bytes', 'megabytes']
            ]
        ];

        yield [
            '100 as megabytes',
            new DisplayAsNode(
                new IntegerNode(100),
                new MemoryUnitNode('megabytes')
            ),
            [
                'memoryUnits' => ['bytes', 'megabytes']
            ]
        ];

        yield [
            'func(100) bytes as megabytes',
            new DisplayAsNode(
                new MemoryValue(
                    new FunctionNode('func', [new IntegerNode(100)]),
                    'bytes'
                ),
                new MemoryUnitNode('megabytes')
            ),
            [
                'memoryUnits' => ['bytes', 'megabytes'],
                'functions' => ['func'],
            ]
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideAggregateFunction(): Generator
    {
        yield 'function' => [
            'mode(variant.time.net)',
            new FunctionNode('mode', [
                new PropertyAccess(['variant', 'time', 'net']),
            ]),
            [
                'functions' => ['mode']
            ]
        ];

        yield 'function with multiple arguments' => [
            'mode(10, 5)',
            new FunctionNode('mode', [
                new IntegerNode(10),
                new IntegerNode(5),
            ]),
            [
                'functions' => ['mode']
            ]
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideExpression(): Generator
    {
        yield 'full comparison' => [
            'mode(foobar.foo) milliseconds > 100 seconds',
            new Comparison(
                new TimeValue(new FunctionNode('mode', [new PropertyAccess(['foobar', 'foo'])]), 'milliseconds'),
                '>',
                new TimeValue(new IntegerNode(100), 'seconds')
            ),
            [
                'functions' => ['mode'],
                'timeUnits' => ['milliseconds', 'seconds'],
            ]
        ];

        yield 'nested function' => [
            'addTwo(mode(10)) milliseconds',
            new TimeValue(
                new FunctionNode(
                    'addTwo',
                    [new FunctionNode(
                        'mode',
                        [new IntegerNode(10)]
                    )]
                ),
                'milliseconds'
            ),
            [
                'functions' => ['mode', 'addTwo'],
                'timeUnits' => ['milliseconds'],
            ]
        ];

        yield 'nested function 2' => [
            'mode(addTwo(mode(10, 20)))',
            new FunctionNode(
                'mode',
                [
                    new FunctionNode(
                    'addTwo',
                    [
                        new FunctionNode('mode', [
                            new IntegerNode(10),
                            new IntegerNode(20),
                        ])
                    ]
                    ),
                ]
            ),
            [
                'functions' => ['mode', 'addTwo'],
                'timeUnits' => ['milliseconds'],
            ]
        ];

        yield 'function with multiple arguments' => [
            'mode(10, 5)',
            new FunctionNode('mode', [
                new IntegerNode(10),
                new IntegerNode(5),
            ]),
            [
                'functions' => ['mode']
            ]
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideTolerance(): Generator
    {
        yield [
            '9 ms > 10 ms +/- 1',
            new Comparison(
                new TimeValue(new IntegerNode(9), 'ms'),
                '>',
                new TimeValue(new IntegerNode(10), 'ms'),
                new ToleranceNode(new IntegerNode(1))
            ),
            [
                'timeUnits' => ['ms'],
            ]
        ];

        yield [
            '9 ms > 10 ms +/- 10%',
            new Comparison(
                new TimeValue(new IntegerNode(9), 'ms'),
                '>',
                new TimeValue(new IntegerNode(10), 'ms'),
                new ToleranceNode(new PercentageValue(new IntegerNode(10)))
            ),
            [
                'timeUnits' => ['ms'],
            ]
        ];
    }

    /**
     * @dataProvider provideSyntaxErrors
     */
    public function testSyntaxErrors(string $expression, string $expectedMessage, array $config = []): void
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->parse($expression, $config);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideSyntaxErrors(): Generator
    {
        yield 'invalid value' => [
            '"!£',
            'Do not know how to parse token'
        ];

        yield 'unknown node' => [
            'foobar()',
            'Unexpected extra'
        ];

        yield [
            '<=',
            'Do not know',
            [
                'functions' => [
                    'func'
                ]
            ]
        ];

        yield [
            'func(10 +/- 10)',
            'Invalid expression',
            [
                'functions' => [
                    'func'
                ]
            ]
        ];

        yield [
            '5 + (5',
            'Expected "close_paren"',
            [
                'functions' => [
                    'func'
                ]
            ]
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideThroughput(): Generator
    {
        yield [
            '5 ops/s = 0.20 seconds',
            new Comparison(
                new ThroughputValue(new IntegerNode(5), new TimeUnitNode('s')),
                '=',
                new TimeValue(new FloatNode(0.20), 'seconds')
            ),
            [
                'timeUnits' => ['s', 'seconds'],
            ]
        ];
        yield 'throughput' => [
            '100000 <= 10 ops/s +/- 1 ops/s',
            new Comparison(
                new IntegerNode(100000),
                '<=',
                new ThroughputValue(new IntegerNode(10), new TimeUnitNode('s')),
                new ToleranceNode(new ThroughputValue(new IntegerNode(1), new TimeUnitNode('s')))
            ),
            [
                'timeUnits' => ['s'],
            ]
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideArithmetic(): Generator
    {
        yield [
            '1 + 1',
            new ArithmeticNode(
                new IntegerNode(1),
                '+',
                new IntegerNode(1)
            )
        ];

        yield [
            '1 + 1 + 2',
            new ArithmeticNode(
                new IntegerNode(1),
                '+',
                new ArithmeticNode(
                    new IntegerNode(1),
                    '+',
                    new IntegerNode(2)
                )
            )
        ];

        yield [
            '(1 + 2) + 3',
            new ArithmeticNode(
                new ParenthesizedExpressionNode(
                    new ArithmeticNode(
                        new IntegerNode(1),
                        '+',
                        new IntegerNode(2),
                    )
                ),
                '+',
                new IntegerNode(3)
            )
        ];

        yield [
            '3 + (1 + 2)',
            new ArithmeticNode(
                new IntegerNode(3),
                '+',
                new ParenthesizedExpressionNode(
                    new ArithmeticNode(
                        new IntegerNode(1),
                        '+',
                        new IntegerNode(2),
                    )
                )
            )
        ];

        yield [
            '3 * 2 + 2* 2',
            new ArithmeticNode(
                new IntegerNode(3),
                '*',
                new ArithmeticNode(
                    new IntegerNode(2),
                    '+',
                    new ArithmeticNode(
                        new IntegerNode(2),
                        '*',
                        new IntegerNode(2),
                    )
                )
            )
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideList(): Generator
    {
        yield [
            '[]',
            new ListNode([])
        ];
        yield [
            '[10]',
            new ListNode([new IntegerNode(10)])
        ];
        yield [
            '[10, [12.12,12]]',
            new ListNode([
                new IntegerNode(10),
                new ListNode([
                    new FloatNode(12.12),
                    new IntegerNode(12)
                ])
            ])
        ];
    }
}
