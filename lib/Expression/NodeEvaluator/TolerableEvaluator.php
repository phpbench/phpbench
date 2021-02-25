<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberValue;
use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<TolerableNode>
 */
class TolerableEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(TolerableNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        $toleranceNode = $node->tolerance();
        $context = $evaluator->evaluateType($node->value(), NumberValue::class, $params);

        if ($toleranceNode instanceof PercentageNode) {
            $amount = $evaluator->evaluateType($toleranceNode->value(), NumberValue::class, $params);
            $tolerance = new FloatNode(
                ($context->value() / 100) * $amount->value()
            );
        } else {
            $tolerance = $evaluator->evaluate($toleranceNode, $params);
        }

        if ($context instanceof DisplayAsNode) {
            $tolerance = new DisplayAsNode($tolerance, $context->as());
        }

        return new TolerableNode(
            $context,
            $tolerance
        );
    }
}
