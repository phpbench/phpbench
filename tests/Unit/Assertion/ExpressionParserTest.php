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
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\FunctionNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
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
            new MemoryValue(new IntegerNode(100), 'bytes', 'megabytes'),
            [
                'memoryUnits' => ['bytes', 'megabytes']
            ]
        ];

        yield [
            '100 as megabytes',
            new MemoryValue(new IntegerNode(100), 'bytes', 'megabytes'),
            [
                'memoryUnits' => ['bytes', 'megabytes']
            ]
        ];

        yield [
            'func(100) bytes as megabytes',
            new MemoryValue(
                new FunctionNode('func', [new IntegerNode(100)]),
                'bytes',
                'megabytes'
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
            '+/- 10',
            new ToleranceNode(new IntegerNode(10))
        ];

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
            'Do not know how to parse token'
        ];

        yield [
            '<=',
            'Left hand side of comparison',
            [
                'functions' => [
                    'func'
                ]
            ]
        ];

        yield [
            'func(10 +/- 10)',
            'Expected token "comma"',
            [
                'functions' => [
                    'func'
                ]
            ]
        ];

        yield [
            'func(10 ms)',
            'Expected token "comma"',
            [
                'timeUnits' => ['ms'],
                'functions' => [
                    'func'
                ]
            ]
        ];

        yield [
            'ms',
            'Time unit expected',
            [
                'timeUnits' => ['ms'],
                'functions' => [
                    'func'
                ]
            ]
        ];
    }
}
