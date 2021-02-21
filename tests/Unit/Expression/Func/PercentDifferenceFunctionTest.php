<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\HistogramFunction;
use PhpBench\Expression\Func\MaxFunction;
use PhpBench\Expression\Func\PercentDifferenceFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class PercentDifferenceFunctionTest extends FunctionTestCase
{
    public function testEval(): void
    {
        self::assertEquals(-50, $this->eval(new PercentDifferenceFunction(), 2, 1));
    }
}
