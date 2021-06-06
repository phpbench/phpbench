<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\StDevFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class StDevFunctionTest extends FunctionTestCase
{
    public function testEvalWithNull(): void
    {
        self::assertEquals(0.5, $this->eval(
            new StDevFunction(),
            '[1, 2, null, null]'
        )->value());
    }
    public function testEval(): void
    {
        self::assertEquals(0.5, $this->eval(
            new StDevFunction(),
            '[1, 2]'
        )->value());
    }
}
