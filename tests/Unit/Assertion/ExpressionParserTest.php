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
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Exception\SyntaxError;

class ExpressionParserTest extends ExpressionParserTestCase
{
    /**
     * @dataProvider provideValues
     * @dataProvider provideComparison
     * @dataProvider provideAggregateFunction
     * @dataProvider provideComparisonThroughput
     */
    public function testParse(string $dsl, Node $expected, array $config): void
    {
        $this->assertEquals($expected, $this->parse($dsl, $config));
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
    }

    /**
     * @return Generator<mixed>
     */
    public function provideComparisonThroughput(): Generator
    {
        yield 'with throughput' => [
            'this.time >= 100 ops/millisecond',
            new Comparison(
                new PropertyAccess(['this', 'time']),
                '>=',
                new ThroughputValue(100, 'millisecond')
            )
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
    }

    /**
     * @dataProvider provideSyntaxErrors
     */
    public function testSyntaxErrors(string $expression, string $expectedMessage): void
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->parse($expression);
    }
        
    /**
     * @return Generator<mixed>
     */
    public function provideSyntaxErrors(): Generator
    {
        yield 'invalid value' => [
                '"!£',
                'Expected property, integer or float'
            ];

        yield 'invalid unit' => [
                '100 foobars',
                'Expected time'
            ];

        yield 'invalid comparator' => [
                '100 microseconds !',
                'Expected comparator, got "!"'
            ];
    }

    /**
     * @dataProvider provideUnits
     */
    public function testTimeUnit(string $unit): void
    {
        $node = $this->parse(sprintf('10 %s > 10 microseconds', $unit));
        self::assertInstanceOf(Comparison::class, $node);
        self::assertEquals($unit, $node->value1()->unit());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideUnits(): Generator
    {
        yield [ 'microseconds' ];

        yield [ 'milliseconds' ];

        yield [ 'ms' ];

        yield [ 'seconds' ];

        yield [ 's' ];

        yield [ 'minutes' ];

        yield [ 'm' ];
    }

    /**
     * @dataProvider provideStorageUnit
     */
    public function testStorageUnit(string $unit): void
    {
        $node = $this->parse(sprintf('10 %s > 10 bytes', $unit));
        self::assertInstanceOf(Comparison::class, $node);
        self::assertEquals($unit, $node->value1()->unit());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideStorageUnit(): Generator
    {
        yield [ 'gigabytes' ];

        yield [ 'gb' ];

        yield [ 'megabytes' ];

        yield [ 'mb' ];

        yield [ 'kilobytes' ];

        yield [ 'k' ];

        yield [ 'kilobytes' ];

        yield [ 'kb' ];

        yield [ 'bytes' ];

        yield [ 'b' ];
    }
}
