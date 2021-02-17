<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PHPUnit\Framework\TestCase;
use PhpBench\Expression\Func\MeanFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class MeanFunctionTest extends FunctionTestCase
{
    public function testEval(): void
    {
        self::assertEquals(4, $this->eval(new MeanFunction(), [2, 4, 6]));
    }
}
