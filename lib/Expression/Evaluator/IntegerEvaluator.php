<?php

namespace PhpBench\Expression\Evaluator;

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

    public function evaluate(Evaluator $evaluator, Node $node): Node
    {
        return $node;
    }
}
