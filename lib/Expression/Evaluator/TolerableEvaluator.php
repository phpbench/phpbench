<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\AbstractEvaluator;
use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\Evaluator;
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

    public function evaluate(Evaluator $evaluator, Node $node)
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
