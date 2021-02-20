<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\NumberValue;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\Ast\ToleratedTrue;
use PhpBench\Expression\Evaluator;
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

    public function evaluate(Evaluator $evaluator, Node $node): Node
    {
        $leftNode = $node->left();
        $rightNode = $node->right();

        $leftValue = $evaluator->evaluateType($node->left(), NumberValue::class);
        $rightValue = $evaluator->evaluate($node->right());

        if ($rightValue instanceof TolerableNode) {
            $toleranceValue = $evaluator->evaluateType($rightValue->tolerance(), NumberNode::class);
            $rightValue = $evaluator->evaluateType($rightValue->value(), NumberNode::class);

            if (FloatNumber::isWithin(
                $leftValue->value(),
                $rightValue->value() - $toleranceValue->value(),
                $rightValue->value() + $toleranceValue->value()
            )) {
                return new ToleratedTrue();
            }
        }

        $rightValue = $evaluator->evaluateType($rightValue, NumberNode::class);

        return new BooleanNode($this->evaluateNode($node, $leftValue->value(), $rightValue->value()));
    }

    /**
     * @param int|float $leftValue
     * @param int|float $rightValue
     */
    private function evaluateNode(ComparisonNode $node, $leftValue, $rightValue): bool
    {
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
