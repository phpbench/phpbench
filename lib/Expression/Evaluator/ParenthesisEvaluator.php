<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\AbstractEvaluator;
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

    public function evaluate(Evaluator $evaluator, Node $node)
    {
        return $evaluator->evaluate($node->expression());
    }
}
