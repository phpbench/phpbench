<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\VarianceFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class VarianceFunctionTest extends FunctionTestCase
{
    public function testEvalWithNull(): void
    {
        self::assertEquals(0.25, $this->eval(
            new VarianceFunction(),
            '[1, 2, null, null]'
        )->value());
    }

    public function testEval(): void
    {
        self::assertEquals(0.25, $this->eval(
            new VarianceFunction(),
            '[1, 2]'
        )->value());
    }
}
