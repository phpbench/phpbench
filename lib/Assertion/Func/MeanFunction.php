<?php

namespace PhpBench\Assertion\Func;

use PhpBench\Math\Statistics;

final class MeanFunction
{
    /**
     * @param numeric[] $values
     */
    public function __invoke(array $values): float
    {
        return Statistics::mean($values);
    }
}
