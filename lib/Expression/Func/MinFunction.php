<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValueFactory;
use RuntimeException;

final class MinFunction
{
    public function __invoke(ListNode $values): Node
    {
        $result = min($values->phpValues());

        if (!is_float($result) && !is_int($result)) {
            throw new RuntimeException(
                'Could not evaluate min'
            );
        }

        return PhpValueFactory::fromNumber($result);
    }
}
