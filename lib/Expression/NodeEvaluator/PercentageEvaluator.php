<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<PercentageNode>
 */
class PercentageEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(PercentageNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        return $node;
    }
}
