<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainEvaluator;

/**
 * @extends AbstractEvaluator<ArgumentListNode>
 */
class ArgumentListEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ArgumentListNode::class);
    }

    public function evaluate(MainEvaluator $evaluator, Node $node)
    {
        return array_map(function (Node $expression) use ($evaluator) {
            return $evaluator->evaluate($expression);
        }, $node->expressions());
    }
}
