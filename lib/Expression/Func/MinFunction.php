<?php

namespace PhpBench\Expression\Func;

use PhpBench\Math\Statistics;
use RuntimeException;

final class MinFunction
{
    /**
     * @param (int|float)[] $values
     * @return int|float
     */
    public function __invoke(array $values)
    {
        $result = min($values);

        if (!is_float($result) && !is_int($result)) {
            throw new RuntimeException(
                'Could not evaluate min'
            );
        }

        return $result;
    }
}
