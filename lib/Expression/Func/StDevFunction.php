<?php

namespace PhpBench\Expression\Func;

use PhpBench\Math\Statistics;

final class StDevFunction
{
    /**
     * @param (int|float)[] $values
     */
    public function __invoke(array $values, bool $sample = false): float
    {
        return Statistics::stdev($values, $sample);
    }
}
