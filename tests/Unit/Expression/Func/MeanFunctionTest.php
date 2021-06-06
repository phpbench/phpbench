<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Func\MeanFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class MeanFunctionTest extends FunctionTestCase
{
    public function testEvalNullValues(): void
    {
        self::assertEquals(4, $this->eval(
            new MeanFunction(),
            '[2, 6, null, null, null]'
        )->value());
    }
    public function testEval(): void
    {
        self::assertEquals(new IntegerNode(4), $this->eval(
            new MeanFunction(),
            "[2, 4, 6]"
        ));
    }
}
