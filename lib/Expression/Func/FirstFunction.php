<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;

final class FirstFunction
{
    public function __invoke(Node $list): Node
    {
        if (!$list instanceof ListNode) {
            return new NullNode();
        }

        $values = $list->nodes();
        $first = reset($values);

        if (!$first) {
            return new NullNode();
        }

        return $first;
    }
}
