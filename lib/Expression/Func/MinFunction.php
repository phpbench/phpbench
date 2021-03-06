<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use RuntimeException;

final class MinFunction
{
    /**
     * @return int|float
     */
    public function __invoke(ListNode $values)
    {
        $result = min($values->phpValues());

        if (!is_float($result) && !is_int($result)) {
            throw new RuntimeException(
                'Could not evaluate min'
            );
        }

        return $result;
    }
}
