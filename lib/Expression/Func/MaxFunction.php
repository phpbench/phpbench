<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use RuntimeException;

final class MaxFunction
{
    /**
     * @param (int|float)[] $values
     *
     * @return int|float
     */
    public function __invoke(ListNode $values)
    {
        $result = max($values->phpValues());

        if (!is_float($result) && !is_int($result)) {
            throw new RuntimeException(
                'Could not evaluate max'
            );
        }

        return PhpValueFactory::fromNumber($result);
    }
}
