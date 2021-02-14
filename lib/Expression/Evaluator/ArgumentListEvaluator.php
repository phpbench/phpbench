<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\AbstractEvaluator;
use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<ArgumentListNode>
 */
class ArgumentListEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ArgumentListNode::class);
    }

    public function evaluate(Evaluator $evaluator, Node $node)
    {
        return array_map(function (Node $expression) use ($evaluator) {
            return $evaluator->evaluate($expression);
        }, $node->expressions());
    }

}
