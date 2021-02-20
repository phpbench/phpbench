<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainEvaluator;

/**
 * @extends AbstractEvaluator<IntegerNode>
 */
class IntegerEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(IntegerNode::class);
    }

    public function evaluate(MainEvaluator $evaluator, Node $node): Node
    {
        return $node;
    }
}
