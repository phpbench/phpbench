<?php

namespace PhpBench\Tests\Unit\Assertion\Ast;

use Generator;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Tests\Unit\Assertion\ExpressionParserTestCase;

class MemoryValueTest extends ExpressionParserTestCase
{
    /**
     * @dataProvider provideMemoryValue
     */
    public function testMemoryValue(
        MemoryValue $memoryValue,
        int $expected
    ): void {
        self::assertEquals(
            $expected,
            $this->evaluate($memoryValue)
        );
    }
    
    /**
     * @return Generator<mixed>
     */
    public function provideMemoryValue(): Generator
    {
        yield [
            new MemoryValue(10, 'bytes'),
            10
        ];
    }
}
