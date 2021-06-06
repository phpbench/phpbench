<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Func\MaxFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class MaxFunctionTest extends FunctionTestCase
{
    public function testEvalNullValues(): void
    {
        self::assertEquals(6, $this->eval(
            new MaxFunction(),
            '[2, 6, null, null, null]'
        )->value());
    }

    public function testEval(): void
    {
        self::assertEquals(new IntegerNode(6), $this->eval(
            new MaxFunction(),
            "[2, 4, 6]"
        ));
    }

    public function testEmptyReturnsNull(): void
    {
        self::assertEquals(
            new NullNode(),
            $this->eval(new MaxFunction(), "[]")
        );
    }
}
