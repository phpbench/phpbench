<?php

namespace PhpBench\Expression\Theme;

use Closure;
use PhpBench\Expression\ColorMap;

/**
 * @template T
 *
 * @implements ColorMap<T>
 */
class ArrayTheme implements ColorMap
{
    /**
     * @var array<class-string<T>, string|Closure(T):string>
     */
    private $map;

    /**
     * @param array<class-string<T>, string|Closure(T):string> $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * {@inheritDoc}
     */
    public function colors(): array
    {
        return $this->map;
    }
}
