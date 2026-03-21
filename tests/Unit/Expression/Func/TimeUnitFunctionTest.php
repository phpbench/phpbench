<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PHPUnit\Framework\Attributes\TestWith;
use PhpBench\Expression\Func\TimeUnitFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;
use PhpBench\Util\TimeUnit;

class TimeUnitFunctionTest extends FunctionTestCase
{
    #[TestWith(['"milliseconds"', "milliseconds"])]
    #[TestWith(['"milliseconds", true', "ms"])]
    #[TestWith(['"ms", true', "ms"])]
    #[TestWith(['"ms", false', "milliseconds"])]
    #[TestWith(['"", true', "μs"])]
    public function testTimeUnit(string $paramExpr, string $expected): void
    {
        self::assertEquals($expected, $this->eval(
            new TimeUnitFunction(new TimeUnit()),
            $paramExpr
        )->value());
    }
}
