<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<NullNode>
 */
class NullEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(NullNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        return $node;
    }
}
