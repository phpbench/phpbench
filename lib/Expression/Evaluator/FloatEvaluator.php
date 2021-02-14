<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Expression\AbstractEvaluator;
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

    public function evaluate(Evaluator $evaluator, Node $node)
    {
        return (float)$node->value();
    }

}
