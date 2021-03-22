<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\CoalesceFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class CoalesceFunctionTest extends FunctionTestCase
{
    public function testCoalesce(): void
    {
        self::assertEquals(10, $this->eval(
            new CoalesceFunction(),
            '10, null'
        )->value());
        self::assertEquals(null, $this->eval(
            new CoalesceFunction(),
            'null, null'
        )->value());
        self::assertEquals(null, $this->eval(
            new CoalesceFunction(),
            ''
        )->value());
        self::assertEquals(null, $this->eval(
            new CoalesceFunction(),
            'null'
        )->value());
        self::assertEquals(12, $this->eval(
            new CoalesceFunction(),
            'null,12,null'
        )->value());
    }
}
