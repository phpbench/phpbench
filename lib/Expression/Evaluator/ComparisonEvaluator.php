<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainEvaluator;
use PhpBench\Expression\Value\TolerableValue;
use PhpBench\Expression\Value\ToleratedValue;
use PhpBench\Math\FloatNumber;

/**
 * @extends AbstractEvaluator<ComparisonNode>
 */
class ComparisonEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ComparisonNode::class);
    }

    public function evaluate(MainEvaluator $evaluator, Node $node)
    {
        $leftNode = $node->left();
        $rightNode = $node->right();

        $leftValue = $evaluator->evaluate($node->left());
        $rightValue = $evaluator->evaluate($node->right());

        if ($rightValue instanceof TolerableValue) {
            if (FloatNumber::isWithin(
                $leftValue,
                $rightValue->value - $rightValue->tolerance,
                $rightValue->value + $rightValue->tolerance
            )) {
                return new ToleratedValue($leftValue);
            }

            $rightValue = $rightValue->value;
        }

        switch ($node->operator()) {
            case '<':
                return $leftValue < $rightValue;
            case '<=':
                return $leftValue <= $rightValue;
            case '=':
                return $leftValue == $rightValue;
            case '>':
                return $leftValue > $rightValue;
            case '>=':
                return $leftValue >= $rightValue;
        }

        throw new ExpressionEvaluatorError(sprintf(
            'Unknown comparison operator "%s"',
            $node->operator()
        ));
    }
}
