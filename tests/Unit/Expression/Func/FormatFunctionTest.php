<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\FormatFunction;
use PhpBench\Expression\Func\MaxFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class FormatFunctionTest extends FunctionTestCase
{
    public function testEval(): void
    {
        self::assertEquals('2 foo 6', $this->eval(
            new FormatFunction(), '%s %s %s', 2, 'foo', 6
        ));
    }
}
