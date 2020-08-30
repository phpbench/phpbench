<?php

namespace PhpBench\Tests\Unit\Assertion\Ast;

use Generator;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Tests\Unit\Assertion\ExpressionParserTestCase;

class ThroughputValueTest extends ExpressionParserTestCase
{
    /**
     * @dataProvider provideThroughputValue
     */
    public function testThroughputValue(
        ThroughputValue $throughputValue,
        float $expected
    ): void {
        self::assertEquals(
            $expected,
            $this->evaluate($throughputValue)
        );
    }
    
    /**
     * @return Generator<mixed>
     */
    public function provideThroughputValue(): Generator
    {
        yield [
            new ThroughputValue(2, 'second'),
            500000
        ];

        yield [
            new ThroughputValue(1, 'millisecond'),
            1000
        ];

        yield [
            new ThroughputValue(2, 'millisecond'),
            500
        ];

        yield [
            new ThroughputValue(2, 'microsecond'),
            0.5
        ];
    }
}
