<?php

namespace PhpBench\Tests\Unit\Assertion\Ast;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Tests\Unit\Assertion\ExpressionParserTestCase;

class ComparisonTest extends ExpressionParserTestCase
{
    /**
     * @dataProvider provideLessThan
     */
    public function testComparison(
        string $expression,
        array $args,
        bool $expected
    ): void
    {
        self::assertEquals(
            $expected,
            $this->evaluateExpression($expression, $args)
        );
    }
    
    /**
     * @return Generator<mixed>
     */
    public function provideLessThan(): Generator
    {
        yield [
            '10 microseconds less than 10 microseconds',
            [],
            false
        ];

        yield [
            '10 microseconds less than 11 microseconds',
            [],
            true
        ];

        yield [
            '9 microseconds less than 8 microseconds',
            [],
            false
        ];

        yield [
            '10 seconds less than 10 seconds',
            [],
            false
        ];

        yield [
            '10 seconds less than 11 seconds',
            [],
            true
        ];
    }
}
