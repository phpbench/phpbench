<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\NumberNodeFactory;
use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainEvaluator;

/**
 * @extends AbstractEvaluator<BinaryOperatorNode>
 */
class BinaryOperatorEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(BinaryOperatorNode::class);
    }

    public function evaluate(MainEvaluator $evaluator, Node $node): Node
    {
        $leftValue = $evaluator->evaluate($node->left(), NumberNode::class);
        $rightValue = $evaluator->evaluate($node->right(), NumberNode::class);

        $value = $this->evaluateNode($node, $leftValue->value(), $rightValue->value());

        return NumberNodeFactory::fromNumber($value);
    }

    private function evaluateNode(Node $node, $leftValue, $rightValue)
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
            case 'or':
                return $leftValue || $rightValue;
            case 'and':
                return $leftValue && $rightValue;
        }
        
        throw new ExpressionEvaluatorError(sprintf(
            'Unknown operator "%s"',
            $node->operator()
        ));
    }
}
