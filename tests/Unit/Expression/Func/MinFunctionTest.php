<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PHPUnit\Framework\TestCase;
use PhpBench\Expression\Func\MeanFunction;
use PhpBench\Expression\Func\MinFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class MinFunctionTest extends FunctionTestCase
{
    public function testEval(): void
    {
        self::assertEquals(2, $this->eval(new MinFunction(), [2, 4, 6]));
    }
}
