<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<DisplayAsNode>
 */
class DisplayAsEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(DisplayAsNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        $value = $evaluator->evaluateType($node->node(), NumberNode::class, $params);
        $unit = $node->as();

        return new DisplayAsNode($value, $unit);
    }
}
