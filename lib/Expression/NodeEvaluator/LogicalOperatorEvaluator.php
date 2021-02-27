<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\LogicalOperatorNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;

/**
 * @extends AbstractEvaluator<LogicalOperatorNode>
 */
class LogicalOperatorEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(LogicalOperatorNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        $leftValue = $evaluator->evaluateType(
            $node->left(),
            PhpValue::class,
            $params
        );
        $rightValue = $evaluator->evaluateType(
            $node->right(),
            PhpValue::class,
            $params
        );

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
        
        throw new EvaluationError($node, sprintf(
            'Unknown operator "%s"',
            $node->operator()
        ));
    }
}
