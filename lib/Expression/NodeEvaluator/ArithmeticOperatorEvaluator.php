<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;

/**
 * @extends AbstractEvaluator<ArithmeticOperatorNode>
 */
class ArithmeticOperatorEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ArithmeticOperatorNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        $leftValue = $evaluator->evaluateType($node->left(), NumberNode::class, $params);
        $rightValue = $evaluator->evaluateType($node->right(), NumberNode::class, $params);

        $value = $this->evaluateNode($node, $leftValue->value(), $rightValue->value());

        return PhpValueFactory::fromNumber($value);
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
        
        throw new EvaluationError($node, sprintf(
            'Unknown operator "%s"',
            $node->operator()
        ));
    }
}
