<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<BooleanNode>
 */
class BooleanEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(BooleanNode::class);
    }

    public function evaluate(Evaluator $evaluator, Node $node): Node
    {
        return $node;
    }
}
