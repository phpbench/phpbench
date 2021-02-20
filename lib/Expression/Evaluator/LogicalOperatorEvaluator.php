<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\LogicalOperatorNode;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\NumberNodeFactory;
use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<LogicalOperatorNode>
 */
class LogicalOperatorEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(LogicalOperatorNode::class);
    }

    public function evaluate(Evaluator $evaluator, Node $node): Node
    {
        $leftValue = $evaluator->evaluate($node->left());
        $rightValue = $evaluator->evaluate($node->right());

        $value = $this->evaluateNode($node, $leftValue->value(), $rightValue->value());

        return new BooleanNode($value);
    }

    private function evaluateNode(Node $node, $leftValue, $rightValue)
    {
        switch ($node->operator()) {
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
