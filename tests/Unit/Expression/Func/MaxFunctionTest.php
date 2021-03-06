<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\MaxFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class MaxFunctionTest extends FunctionTestCase
{
    public function testEval(): void
    {
        self::assertEquals(6, $this->eval(
            new MaxFunction(),
            "[2, 4, 6]"
        ));
    }
}
