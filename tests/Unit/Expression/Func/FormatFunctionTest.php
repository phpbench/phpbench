<?php

namespace PhpBench\Tests\Unit\Expression\Func;

use PhpBench\Expression\Func\FormatFunction;
use PhpBench\Tests\Unit\Expression\FunctionTestCase;
use RuntimeException;

class FormatFunctionTest extends FunctionTestCase
{
    public function testFormat(): void
    {
        self::assertEquals('2 foo 6', $this->eval(
            new FormatFunction(), '%s %s %s', 2, 'foo', 6
        ));
    }

    public function testTooManyPlaceholders(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('format()');
        self::assertEquals('2 foo 6', $this->eval(
            new FormatFunction(), '%s %s %s %s', 2, 'foo', 6
        ));
    }
}
