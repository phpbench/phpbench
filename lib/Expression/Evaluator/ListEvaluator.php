<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainEvaluator;

/**
 * @extends AbstractEvaluator<ListNode>
 */
class ListEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ListNode::class);
    }

    public function evaluate(MainEvaluator $evaluator, Node $node): Node
    {
        return new ListNode($evaluator->evaluate($left), $evaluator->evaluate($right));
    }
}
