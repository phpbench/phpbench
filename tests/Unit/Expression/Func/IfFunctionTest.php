<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PHPUnit\Framework\Attributes\TestWith;
use PhpBench\Expression\Func\IfFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class IfFunctionTest extends FunctionTestCase
{
    #[TestWith(['true, "foo", 42', 'foo'])]
    #[TestWith(['false, "foo", 42', 42])]
    #[TestWith(['true, [1,2], 42', [1,2]])]
    #[TestWith(['"true", true, false', true])]
    #[TestWith(['"", true, false', false])]
    #[TestWith(['0, true, false', false])]
    #[TestWith(['1, true, false', true])]
    #[TestWith(['false, foo["not_existing"], false', false])] // lazy evaluation so invalid expr not executed
    public function testFormat(string $paramExpr, mixed $expected): void
    {
        self::assertEquals($expected, $this->eval(
            new IfFunction(),
            $paramExpr,
        )->value());
    }
}
