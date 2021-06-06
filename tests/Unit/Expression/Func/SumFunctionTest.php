<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\SumFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class SumFunctionTest extends FunctionTestCase
{
    public function testEvalWithNull(): void
    {
        self::assertEquals(3, $this->eval(
            new SumFunction(),
            '[1, 2, null, null]'
        )->value());
    }

    public function testEval(): void
    {
        self::assertEquals(3, $this->eval(
            new SumFunction(),
            '[1, 2]'
        )->value());
    }
}
