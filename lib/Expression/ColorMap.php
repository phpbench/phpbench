<?php

namespace PhpBench\Expression;

use Closure;

/**
 * @template T
 */
interface ColorMap
{
    /**
     * @return array<class-string<T>, string|Closure(T):string>
     */
    public function colors(): array;
}
