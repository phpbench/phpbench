<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberValue;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\Ast\ToleratedTrue;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\NodeEvaluator;
use PhpBench\Math\FloatNumber;

class ComparisonEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof ComparisonNode) {
            return null;
        }

        $leftNode = $node->left();
        $rightNode = $node->right();

        $leftValue = $evaluator->evaluateType($node->left(), PhpValue::class, $params);
        $rightValue = $evaluator->evaluate($node->right(), $params);

        if ($rightValue instanceof TolerableNode) {
            $toleranceValue = $evaluator->evaluateType($rightValue->tolerance(), NumberValue::class, $params);
            $rightValue = $evaluator->evaluateType($rightValue->value(), NumberValue::class, $params);

            if (FloatNumber::isWithin(
                $leftValue->value(),
                $rightValue->value() - $toleranceValue->value(),
                $rightValue->value() + $toleranceValue->value()
            )) {
                return new ToleratedTrue();
            }
        }

        $rightValue = $evaluator->evaluateType($rightValue, PhpValue::class, $params);

        return new BooleanNode($this->evaluateNode($node, $leftValue->value(), $rightValue->value()));
    }

    /**
     * @param string|int|float $leftValue
     * @param string|int|float $rightValue
     */
    private function evaluateNode(ComparisonNode $node, $leftValue, $rightValue): bool
    {
        if ($node->operator() == '=') {
            return $leftValue == $rightValue;
        }

        if (!is_numeric($leftValue) || !is_numeric($rightValue)) {
            throw new EvaluationError($node, sprintf(
                'Unsupported operator "%s" when comparing "%s" and "%s"',
                $node->operator(),
                gettype($leftValue),
                gettype($rightValue)
            ));
        }

        switch ($node->operator()) {
            case '<':
                return $leftValue < $rightValue;
            case '<=':
                return $leftValue <= $rightValue;
            case '>':
                return $leftValue > $rightValue;
            case '>=':
                return $leftValue >= $rightValue;
        }

        throw new EvaluationError($node, sprintf(
            'Unknown comparison operator "%s"',
            $node->operator()
        ));
    }
}
