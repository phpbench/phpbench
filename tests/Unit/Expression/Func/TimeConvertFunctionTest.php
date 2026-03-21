<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PHPUnit\Framework\Attributes\TestWith;
use PhpBench\Expression\Func\TimeConvertFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;
use PhpBench\Util\TimeUnit;

class TimeConvertFunctionTest extends FunctionTestCase
{
    #[TestWith(['1000, "milliseconds", "seconds"', 1])]
    #[TestWith(['1, "seconds", "ms"', 1000])]
    #[TestWith(['1, "s", "ms"', 1000])]
    public function testTimeUnit(string $paramExpr, int|float $expected): void
    {
        self::assertEquals($expected, $this->eval(
            new TimeConvertFunction(new TimeUnit()),
            $paramExpr
        )->value());
    }
}
