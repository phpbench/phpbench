<?php

namespace PhpBench\Tests\Unit\Expression;

use PHPUnit\Framework\TestCase;

class FunctionTestCase extends TestCase
{
    public function eval(callable $callable, ...$args)
    {
        return $callable(...$args);
    }
}
