<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainEvaluator;

/**
 * @extends AbstractEvaluator<FloatNode>
 */
class FloatEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(FloatNode::class);
    }

    public function evaluate(MainEvaluator $evaluator, Node $node)
    {
        return (float)$node->value();
    }
}
