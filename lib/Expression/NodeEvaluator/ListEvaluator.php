<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<ListNode>
 */
class ListEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ListNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        return new ListNode(array_map(function (Node $node) use ($evaluator, $params) {
            return $evaluator->evaluate($node, $params);
        }, $node->value()));
    }
}
