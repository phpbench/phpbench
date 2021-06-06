<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\PhpValueFactory;

final class MinFunction
{
    public function __invoke(ListNode $values): PhpValue
    {
        $values = $values->nonNullPhpValues();

        if (empty($values)) {
            return new NullNode();
        }

        $result = min($values);

        if (!is_float($result) && !is_int($result)) {
            return new NullNode();
        }

        return PhpValueFactory::fromValue($result);
    }
}
