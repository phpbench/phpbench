<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use ArgumentCountError;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Func\FirstFunction;
use PhpBench\Expression\Func\FormatFunction;
use PhpBench\Expression\Func\JoinFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;
use RuntimeException;

class FirstFunctionTest extends FunctionTestCase
{
    public function testFirst(): void
    {
        self::assertEquals('one', $this->eval(
            new FirstFunction(),
            '["one", "two"]'
        )->value());
    }

    public function testFirstEmpty(): void
    {
        $this->expectException(EvaluationError::class);

        $this->eval(new FirstFunction(), '[]')->value();
    }
}
