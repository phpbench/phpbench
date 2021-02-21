<?php

namespace PhpBench\Expression\Func;

use PhpBench\Math\Statistics;

final class VarianceFunction
{
    /**
     * @param (int|float)[] $values
     * @return float
     */
    public function __invoke(array $values, bool $sample = false): float
    {
        return Statistics::variance($values, $sample);
    }
}

