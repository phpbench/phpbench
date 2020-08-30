<?php

namespace PhpBench\Tests\Unit\Assertion\Ast;

use Generator;
use PhpBench\Assertion\AssertionResult;
use PhpBench\Tests\Unit\Assertion\ExpressionParserTestCase;

class ComparisonTest extends ExpressionParserTestCase
{
    /**
     * @dataProvider provideLessThan
     * @dataProvider provideLessThanOrEqual
     * @dataProvider provideEqual
     * @dataProvider provideGreaterThan
     * @dataProvider provideGreaterThanEqual
     * @dataProvider provideTolerance
     * @dataProvider provideThroughput
     */
    public function testComparison(
        string $expression,
        AssertionResult $result
    ): void {
        self::assertEquals(
            $result,
            $this->evaluateExpression($expression, [])
        );
    }
    
    /**
     * @return Generator<mixed>
     */
    public function provideLessThan(): Generator
    {
        yield [
            '10 microseconds < 10 microseconds',
            AssertionResult::fail()
        ];

        yield [
            '10 microseconds < 11 microseconds',
            AssertionResult::ok()
        ];

        yield [
            '9 microseconds < 8 microseconds',
            AssertionResult::fail()
        ];

        yield [
            '10 seconds < 10 seconds',
            AssertionResult::fail()
        ];

        yield [
            '10 seconds < 11 seconds',
            AssertionResult::ok()
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideLessThanOrEqual(): Generator
    {
        yield [
            '10 seconds <= 10 seconds',
            AssertionResult::ok()
        ];

        yield [
            '10 seconds <= 9 seconds',
            AssertionResult::fail()
        ];

        yield 'tolerated' => [
            '10 seconds <= 9.9 seconds +/- 0.1 seconds',
            AssertionResult::tolerated()
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideEqual(): Generator
    {
        yield [
            '10 seconds = 10 seconds',
            AssertionResult::ok()
        ];

        yield [
            '10 seconds = 11 seconds',
            AssertionResult::fail()
        ];

        yield [
            '10 seconds = 9 seconds',
            AssertionResult::fail()
        ];

        yield [
            '10 seconds = 9 seconds +/- 1 seconds',
            AssertionResult::tolerated()
        ];

        yield [
            '10 seconds = 11 seconds +/- 1 seconds',
            AssertionResult::tolerated()
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideGreaterThan(): Generator
    {
        yield [
            '10 seconds > 9 seconds',
            AssertionResult::ok()
        ];

        yield [
            '10 seconds > 10 seconds',
            AssertionResult::fail()
        ];

        yield [
            '10 seconds > 11 seconds',
            AssertionResult::fail()
        ];

        yield [
            '10 seconds > 10 seconds +/- 1 seconds',
            AssertionResult::tolerated()
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideGreaterThanEqual(): Generator
    {
        yield [
            '10 seconds >= 10 seconds',
            AssertionResult::ok()
        ];

        yield [
            '10 seconds >= 11 seconds',
            AssertionResult::fail()
        ];

        yield [
            '10 seconds >= 10.1 seconds',
            AssertionResult::fail()
        ];

        yield 'tolerated' => [
            '10 seconds >= 10.1 seconds +/- 0.1 seconds',
            AssertionResult::tolerated()
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideThroughput(): Generator
    {
        yield 'throughput 1' => [
            '11 ops/second > 10 ops/second',
            AssertionResult::ok()
        ];

        yield 'throughput 2' => [
            '9 ops/second > 10 ops/second',
            AssertionResult::fail()
        ];

        yield 'throughput 3' => [
            '9 ops/second < 10 ops/second',
            AssertionResult::ok()
        ];

        yield 'throughput 4' => [
            '11 ops/second < 10 ops/second',
            AssertionResult::fail()
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideTolerance(): Generator
    {
        yield [
            '11 seconds <= 10 seconds +/- 0%',
            AssertionResult::fail()
        ];

        yield '11 microseconds <= 10 microseconds +/- 10%' => [
            '11 microseconds <= 10 microseconds +/- 10%',
            AssertionResult::tolerated()
        ];

        yield '11 microseconds <= 10 microseconds +/- 9.999%' => [
            '11 microseconds <= 10 microseconds +/- 9.999%',
            AssertionResult::fail()
        ];

        yield '11 microseconds <= 10 microseconds +/- 10.001%' => [
            '11 microseconds <= 10 microseconds +/- 10.001%',
            AssertionResult::tolerated()
        ];
    }
}
