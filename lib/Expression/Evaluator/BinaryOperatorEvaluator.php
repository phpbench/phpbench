<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Expression\AbstractEvaluator;
use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<BinaryOperatorNode>
 */
class BinaryOperatorEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(BinaryOperatorNode::class);
    }

    public function evaluate(Evaluator $evaluator, Node $node)
    {
        $leftValue = $evaluator->evaluate($node->left());
        $rightValue = $evaluator->evaluate($node->right());

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
