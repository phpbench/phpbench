<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\NumberNodeFactory;
use PhpBench\Expression\Evaluator;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;

/**
 * @extends AbstractEvaluator<DisplayAsNode>
 */
class DisplayAsEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(DisplayAsNode::class);
    }

    public function evaluate(Evaluator $evaluator, Node $node): Node
    {
        $value = $evaluator->evaluateType($node->node(), NumberNode::class);
        $unit = $node->as();

        return new DisplayAsNode($value, $unit);
    }
}

