<?php

namespace PhpBench\Expression\Func;

use PhpBench\Math\Statistics;

final class RStDevFunction
{
    /**
     * @param (int|float)[] $values
     */
    public function __invoke(array $values, bool $sample = false): float
    {
        return Statistics::rstdev($values, $sample);
    }
}
