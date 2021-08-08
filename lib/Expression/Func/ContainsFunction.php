<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\ScalarValue;

final class ContainsFunction
{
    public function __invoke(ScalarValue $value, ListNode $list): BooleanNode
    {
        return new BooleanNode(in_array($value->value(), $list->value(), true));
    }
}
