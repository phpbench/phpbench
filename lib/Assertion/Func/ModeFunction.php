<?php

namespace PhpBench\Assertion\Func;

use PhpBench\Math\Statistics;

final class ModeFunction
{
    /**
     * @param numeric[] $values
     */
    public function __invoke(array $values, int $space = 512): float
    {
        return Statistics::kdeMode($values, $space);
    }
}
