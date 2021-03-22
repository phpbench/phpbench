<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use ArgumentCountError;
use PhpBench\Expression\Func\CoalesceFunction;
use PhpBench\Expression\Func\FormatFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;
use RuntimeException;

class CoalesceFunctionTest extends FunctionTestCase
{
    public function testCoalesce(): void
    {
        self::assertEquals('10', $this->eval(
            new CoalesceFunction(),
            '10, null'
        )->value());
    }
}
