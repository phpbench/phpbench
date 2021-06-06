<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\RStDevFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class RStDevFunctionTest extends FunctionTestCase
{
    public function testEvalWithNull(): void
    {
        self::assertEqualsWithDelta(33.33, $this->eval(
            new RStDevFunction(),
            '[1, 2, null, null]'
        )->value(), 0.1);
    }

    public function testEval(): void
    {
        self::assertEqualsWithDelta(33.33, $this->eval(
            new RStDevFunction(),
            '[1, 2]'
        )->value(), 0.1);
    }
}
