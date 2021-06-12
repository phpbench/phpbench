<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\FirstFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class FirstFunctionTest extends FunctionTestCase
{
    public function testFirst(): void
    {
        self::assertEquals('one', $this->eval(
            new FirstFunction(),
            '["one", "two"]'
        )->value());
    }

    public function testReturnsEmpty(): void
    {
        self::assertEquals(null, $this->eval(new FirstFunction(), '[]')->value());
    }

    public function testReturnsEmptyIfFirstIsNull(): void
    {
        self::assertEquals(null, $this->eval(new FirstFunction(), 'null')->value());
    }
}
