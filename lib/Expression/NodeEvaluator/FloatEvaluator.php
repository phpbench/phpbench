<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<FloatNode>
 */
class FloatEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(FloatNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        return $node;
    }
}
