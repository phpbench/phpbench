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

    public function evaluate(Evaluator $evaluator, Node $node): Node
    {
        $right = $node->right();

        return new ListNode($evaluator->evaluate($node->left()), $right ? $evaluator->evaluate($right) : null);
    }
}
