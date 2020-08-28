<?php

namespace PhpBench\Tests\Unit\Assertion\Ast;

use Generator;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\Value;
use PhpBench\Assertion\Ast\WithinRangeOf;
use PhpBench\Tests\Unit\Assertion\ExpressionParserTestCase;

class WithinRangeOfTest extends ExpressionParserTestCase
{
    /**
     * @dataProvider provideMicroseconds
     * @dataProvider providePercent
     */
    public function testWithin(Value $param1, Value $threshold, Value $param2, array $args = [], bool $expected): void
    {
        self::assertEquals($expected, $this->evaluate(
            new WithinRangeOf($param1, $threshold, $param2),
            $args
        ));
    }
    
    /**
     * @return Generator<mixed>
     */
    public function provideMicroseconds(): Generator
    {
        yield [
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(20),
            TimeValue::fromMicroseconds(-10),
            [],
            true
        ];

        yield [
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(-11),
            [],
            false
        ];

        yield [
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(10),
            [],
            true
        ];

        yield [
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(20),
            [],
            true
        ];

        yield [
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(21),
            [],
            false
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function providePercent(): Generator
    {
        yield '10 percent more' => [
            TimeValue::fromMicroseconds(10),
            new PercentageValue(10),
            TimeValue::fromMicroseconds(11),
            [],
            true
        ];

        yield 'two' => [
            TimeValue::fromMicroseconds(10),
            new PercentageValue(10),
            TimeValue::fromMicroseconds(11.5),
            [],
            false
        ];

        yield 'three' => [
            TimeValue::fromMicroseconds(-100),
            new PercentageValue(10),
            TimeValue::fromMicroseconds(-110),
            [],
            true
        ];
    }
}
