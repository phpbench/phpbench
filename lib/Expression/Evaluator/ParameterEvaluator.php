<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<ParameterNode>
 */
class ParameterEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ParameterNode::class);
    }

    public function evaluate(Evaluator $evaluator, Node $node): Node
    {
        return $node;
    }
}

