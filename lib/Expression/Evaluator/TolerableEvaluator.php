<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\MainEvaluator;
use PhpBench\Expression\Value\TolerableValue;

/**
 * @extends AbstractEvaluator<TolerableNode>
 */
class TolerableEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(TolerableNode::class);
    }

    public function evaluate(MainEvaluator $evaluator, Node $node): Node
    {
        $toleranceNode = $node->tolerance();
        $context = $evaluator->evaluate($node->value());

        if ($toleranceNode instanceof PercentageNode) {
            $tolerance = $context / 100 * $context;
        } else {
            $tolerance = $evaluator->evaluate($toleranceNode);
        }

        return new TolerableValue(
            $context,
            $tolerance
        );
    }
}
