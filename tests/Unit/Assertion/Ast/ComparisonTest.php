<?php

namespace PhpBench\Tests\Unit\Assertion\Ast;

use Generator;
use PhpBench\Assertion\AssertionResult;
use PhpBench\Tests\Unit\Assertion\ExpressionParserTestCase;

class ComparisonTest extends ExpressionParserTestCase
{
    /**
     * @dataProvider provideLessThan
     */
    public function testComparison(
        string $expression,
        array $args,
        AssertionResult $result
    ): void {
        self::assertEquals(
            $result,
            $this->evaluateExpression($expression, $args)
        );
    }
    
    /**
     * @return Generator<mixed>
     */
    public function provideLessThan(): Generator
    {
        yield [
            '10 microseconds < 10 microseconds',
            [],
            AssertionResult::fail()
        ];

        yield [
            '10 microseconds < 11 microseconds',
            [],
            AssertionResult::ok()
        ];

        yield [
            '9 microseconds < 8 microseconds',
            [],
            AssertionResult::fail()
        ];

        yield [
            '10 seconds < 10 seconds',
            [],
            AssertionResult::fail()
        ];

        yield [
            '10 seconds < 11 seconds',
            [],
            AssertionResult::ok()
        ];
    }
}
