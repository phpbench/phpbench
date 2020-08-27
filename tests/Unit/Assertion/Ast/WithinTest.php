<?php

namespace PhpBench\Tests\Unit\Assertion\Ast;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\Ast\Arguments;
use PhpBench\Assertion\Ast\Parameter;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\Within;

class WithinTest extends TestCase
{
    /**
     * @dataProvider provideMicroseconds
     * @dataProvider providePercent
     */
    public function testWithin(Parameter $threshold, Parameter $param1, Parameter $param2, array $args = [], bool $expected): void
    {
        self::assertEquals($expected, (new Within($threshold))->isSatisfiedBy(
            $param1,
            $param2,
            new Arguments($args)
        ));
    }
    
    /**
     * @return Generator<mixed>
     */
    public function provideMicroseconds(): Generator
    {
        yield [
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(0),
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
            new PercentageValue(10),
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(11),
            [],
            true
        ];
        yield [
            new PercentageValue(10),
            TimeValue::fromMicroseconds(10),
            TimeValue::fromMicroseconds(11.5),
            [],
            false
        ];
        yield [
            new PercentageValue(10),
            TimeValue::fromMicroseconds(-100),
            TimeValue::fromMicroseconds(-110),
            [],
            true
        ];
    }
}
