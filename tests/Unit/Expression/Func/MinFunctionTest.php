<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Func\MinFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class MinFunctionTest extends FunctionTestCase
{
    public function testEvalNullValues(): void
    {
        self::assertEquals(2, $this->eval(
            new MinFunction(),
            '[2, 6, null, null, null]'
        )->value());
    }

    public function testEval(): void
    {
        self::assertEquals(
            new IntegerNode(2),
            $this->eval(new MinFunction(), "[2, 4, 6]")
        );
    }

    public function testEmptyReturnsNull(): void
    {
        self::assertEquals(
            new NullNode(),
            $this->eval(new MinFunction(), "[]")
        );
    }
}
