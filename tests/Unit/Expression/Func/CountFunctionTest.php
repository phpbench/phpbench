<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\CountFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class CountFunctionTest extends FunctionTestCase
{
    public function testEvalWithNull(): void
    {
        self::assertEquals(2, $this->eval(
            new CountFunction(),
            '[1, 2, null, null]'
        )->value());
    }

    public function testEval(): void
    {
        self::assertEquals(2, $this->eval(
            new CountFunction(),
            '[1, 2]'
        )->value());
    }
}
