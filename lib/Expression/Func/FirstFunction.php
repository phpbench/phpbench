<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;

final class FirstFunction
{
    public function __invoke(ListNode $list): Node
    {
        $values = $list->value();
        $first = reset($values);

        if (!$first) {
            return new NullNode();
        }

        return $first;
    }
}
