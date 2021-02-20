<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\LogicalOperatorNode;
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
        $leftValue = $evaluator->evaluateType($node->left(), BooleanNode::class);
        $rightValue = $evaluator->evaluateType($node->right(), BooleanNode::class);

        $value = $this->evaluateNode($node, $leftValue->value(), $rightValue->value());

        return new BooleanNode($value);
    }

    private function evaluateNode(LogicalOperatorNode $node, bool $leftValue, bool $rightValue): bool
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
