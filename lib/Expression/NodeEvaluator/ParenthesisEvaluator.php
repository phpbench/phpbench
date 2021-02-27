<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<ParenthesisNode>
 */
class ParenthesisEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ParenthesisNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        return $evaluator->evaluate($node->expression(), $params);
    }
}
