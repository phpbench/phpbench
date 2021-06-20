<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\StringNode;

final class JoinFunction
{
    public function __invoke(StringNode $delimiter, ListNode $values): StringNode
    {
        return new StringNode(
            implode(
                $delimiter->value(),
                $values->value()
            )
        );
    }
}
