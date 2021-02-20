<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\NumberNodeFactory;
use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\MainEvaluator;

/**
 * @extends AbstractEvaluator<ParenthesisNode>
 */
class ParenthesisEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ParenthesisNode::class);
    }

    public function evaluate(MainEvaluator $evaluator, Node $node): Node
    {
        return $evaluator->evaluate($node->expression());
    }
}
