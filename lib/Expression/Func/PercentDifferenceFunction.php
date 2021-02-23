<?php

namespace PhpBench\Expression\Func;

use PhpBench\Math\Statistics;

final class PercentDifferenceFunction
{
    public function __invoke(float $value1, float $value2): float
    {
        return Statistics::percentageDifference($value1, $value2);
    }
}
