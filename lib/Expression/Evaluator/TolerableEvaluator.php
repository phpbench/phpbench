<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Value\TolerableValue;
use PhpBench\Math\FloatNumber;

/**
 * @extends AbstractEvaluator<TolerableNode>
 */
class TolerableEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(TolerableNode::class);
    }

    public function evaluate(Evaluator $evaluator, Node $node): Node
    {
        $toleranceNode = $node->tolerance();
        $context = $evaluator->evaluate($node->value(), NumberNode::class);

        if ($toleranceNode instanceof PercentageNode) {
            $tolerance = new FloatNode($context->value() / 100 * $context->value());
        } else {
            $tolerance = $evaluator->evaluate($toleranceNode);
        }

        return new TolerableNode(
            $context,
            $tolerance
        );
    }
}
