<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator\AbstractEvaluator;

/**
 * @extends AbstractEvaluator<BooleanNode>
 */
class BooleanEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(BooleanNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        return $node;
    }
}
