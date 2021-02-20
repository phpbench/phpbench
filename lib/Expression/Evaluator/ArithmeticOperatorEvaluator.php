<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\NumberNodeFactory;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<ArithmeticOperatorNode>
 */
class ArithmeticOperatorEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ArithmeticOperatorNode::class);
    }

    public function evaluate(Evaluator $evaluator, Node $node): Node
    {
        $leftValue = $evaluator->evaluateType($node->left(), NumberNode::class);
        $rightValue = $evaluator->evaluateType($node->right(), NumberNode::class);

        $value = $this->evaluateNode($node, $leftValue->value(), $rightValue->value());

        return NumberNodeFactory::fromNumber($value);
    }

    /**
     * @param int|float $leftValue
     * @param int|float $rightValue
     *
     * @return int|float
     */
    private function evaluateNode(ArithmeticOperatorNode $node, $leftValue, $rightValue)
    {
        switch ($node->operator()) {
            case '+':
                return $leftValue + $rightValue;
            case '*':
                return $leftValue * $rightValue;
            case '/':
                return $leftValue / $rightValue;
            case '-':
                return $leftValue - $rightValue;
        }
        
        throw new ExpressionEvaluatorError(sprintf(
            'Unknown operator "%s"',
            $node->operator()
        ));
    }
}
