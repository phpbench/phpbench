<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\ModeFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class ModeFunctionTest extends FunctionTestCase
{
    public function testEval(): void
    {
        self::assertEqualsWithDelta(3.99, $this->eval(
            new ModeFunction(),
            '[2, 4, 6]'
        )->value(), 0.1);
    }
}
