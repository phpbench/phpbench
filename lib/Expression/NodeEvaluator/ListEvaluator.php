<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;

class ListEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof ListNode) {
            return null;
        }

        return new ListNode(array_map(function (Node $node) use ($evaluator, $params) {
            return $evaluator->evaluate($node, $params);
        }, $node->nodes()));
    }
}
