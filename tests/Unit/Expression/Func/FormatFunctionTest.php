<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Func\FormatFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class FormatFunctionTest extends FunctionTestCase
{
    public function testFormat(): void
    {
        self::assertEquals('2 foo 6', $this->eval(
            new FormatFunction(),
            '"%s %s %s", 2, "foo", 6'
        )->value());
    }

    public function testTooManyPlaceholders(): void
    {
        $this->expectException(EvaluationError::class);
        $this->eval(
            new FormatFunction(),
            '"%s %s %s %s", 2, "foo", 6'
        );
    }
}
