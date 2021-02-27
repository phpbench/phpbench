<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<IntegerNode>
 */
class IntegerEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(IntegerNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        return $node;
    }
}
