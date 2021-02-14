<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Expression\AbstractEvaluator;
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

    public function evaluate(Evaluator $evaluator, Node $node)
    {
        return (int)$node->value();
    }

}
