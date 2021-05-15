<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\PhpValueFactory;
use RuntimeException;

final class MinFunction
{
    public function __invoke(ListNode $values): PhpValue
    {
        $values = $values->phpValues();

        if (empty($values)) {
            return new NullNode();
        }

        $result = min($values);

        if (!is_float($result) && !is_int($result)) {
            throw new RuntimeException(
                'Could not evaluate min'
            );
        }

        return PhpValueFactory::fromValue($result);
    }
}
