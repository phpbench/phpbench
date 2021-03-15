<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Exception\EvaluationError;

final class FirstFunction
{
    public function __invoke(ListNode $list): Node
    {
        $values = $list->value();
        $first = reset($values);

        if (!$first) {
            throw new EvaluationError($list, 'List is empty, cannot get first');
        }

        return $first;
    }
}
