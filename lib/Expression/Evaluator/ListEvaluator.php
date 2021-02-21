<?php

namespace PhpBench\Expression\Evaluator;

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
        $right = $node->right();

        return new ListNode($evaluator->evaluate(
            $node->left(),
            $params
        ), $right ? $evaluator->evaluate($right, $params) : null);
    }
}
