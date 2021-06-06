<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\PhpValue;

final class CountFunction
{
    public function __invoke(ListNode $values): PhpValue
    {
        return new IntegerNode(count($values->nonNullPhpValues()));
    }
}
