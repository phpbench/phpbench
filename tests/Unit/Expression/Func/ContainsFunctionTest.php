<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\ContainsFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;

class ContainsFunctionTest extends FunctionTestCase
{
    public function testTrueIfContainsInt(): void
    {
        self::assertEquals(true, $this->eval(
            new ContainsFunction(),
            '[1, 2, null, null], 1'
        )->value());
    }
    public function testTrueIfContainsInt2(): void
    {
        self::assertEquals(true, $this->eval(
            new ContainsFunction(),
            '[2, 1, null, null], 1'
        )->value());
    }

    public function testFalseIfNotContainsInt(): void
    {
        self::assertEquals(false, $this->eval(
            new ContainsFunction(),
            '[2, 3, null, null], 1'
        )->value());
    }

    public function testContainsStringFalse(): void
    {
        self::assertEquals(false, $this->eval(
            new ContainsFunction(),
            '[2, 3, null, null],"1"'
        )->value());
    }

    public function testContainsStringTrue(): void
    {
        self::assertEquals(true, $this->eval(
            new ContainsFunction(),
            '[2, 3, "1"], "1"'
        )->value());
    }
}
