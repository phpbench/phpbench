<?php

namespace PhpBench\Expression\Func;

use RuntimeException;

final class MaxFunction
{
    /**
     * @param (int|float)[] $values
     *
     * @return int|float
     */
    public function __invoke(array $values)
    {
        $result = max($values);

        if (!is_float($result) && !is_int($result)) {
            throw new RuntimeException(
                'Could not evaluate max'
            );
        }

        return $result;
    }
}
