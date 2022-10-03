<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\PhpValueFactory;

use function array_sum;

final class SumFunction
{
    public function __invoke(ListNode $values): PhpValue
    {
        return PhpValueFactory::fromValue(array_sum($values->nonNullPhpValues()));
    }
}
