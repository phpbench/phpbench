<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Func\MinFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class MinFunctionTest extends FunctionTestCase
{
    public function testEval(): void
    {
        self::assertEquals(
            new IntegerNode(2),
            $this->eval(new MinFunction(), "[2, 4, 6]")
        );
    }
}
