<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\PhpValueFactory;
use RuntimeException;

final class MaxFunction
{
    public function __invoke(ListNode $values): PhpValue
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
