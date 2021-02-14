<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Assertion\Ast\Node;
use PhpBench\Expression\AbstractEvaluator;
use PhpBench\Expression\Ast\ListNode;
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

    public function evaluate(Evaluator $evaluator, Node $node)
    {
        return array_map(function (Node $expression) use ($evaluator) {
            return $evaluator->evaluate($expression);
        }, $node->expressions());
    }
}
