<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\ScalarValue;

final class ContainsFunction
{
    public function __invoke(ListNode $list, ScalarValue $value): BooleanNode
    {
        return new BooleanNode(in_array($value->value(), $list->value(), true));
    }
}
