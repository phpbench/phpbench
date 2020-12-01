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
     * @dataProvider provideComparison
     * @dataProvider provideComparisonThroughput
     */
    public function testParse(string $dsl, Node $expected): void
    {
        $this->assertEquals($expected, $this->parse($dsl));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideComparison(): Generator
    {
        yield [
            'this.foobar < 100 microseconds',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '<',
                new TimeValue(100, 'microseconds')
            )
        ];

        yield [
            'this.foobar < 100 microseconds',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '<',
                new TimeValue(100, 'microseconds')
            )
        ];

        yield 'less than equal' => [
            'this.foobar <= 100 microseconds',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '<=',
                new TimeValue(100, 'microseconds')
            )
        ];

        yield 'equal' => [
            'this.foobar = 100 microseconds',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '=',
                new TimeValue(100, 'microseconds')
            )
        ];

        yield 'greater than' => [
            'this.foobar > 100 microseconds',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '>',
                new TimeValue(100, 'microseconds')
            )
        ];

        yield 'greater than equal' => [
            'this.foobar >= 100 microseconds',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '>=',
                new TimeValue(100, 'microseconds')
            )
        ];

        yield 'with tolerance' => [
            'this.foobar >= 100 microseconds +/- 10 microseconds',
            new Comparison(
                new PropertyAccess(['this', 'foobar']),
                '>=',
                new TimeValue(100, 'microseconds'),
                new TimeValue(10, 'microseconds')
            )
        ];

        yield 'with tolerance percentage' => [
            'this.mem_peak >= 100 bytes +/- 10%',
            new Comparison(
                new PropertyAccess(['this', 'mem_peak']),
                '>=',
                new MemoryValue(100, 'bytes'),
                new PercentageValue(10)
            )
        ];

        yield 'with no tolerance unit' => [
            'this.mode < 1 microsecond +/- 1000',
            new Comparison(
                new PropertyAccess(['this', 'mode']),
                '<',
                new TimeValue(1, 'microsecond'),
                new TimeValue(1000, 'microseconds')
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
