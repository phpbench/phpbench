<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\ArrayAccessNode;
use PhpBench\Expression\Ast\ConcatenatedNode;
use PhpBench\Expression\Ast\ConcatNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\ScalarValue;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\ExpressionError;
use PhpBench\Expression\NodeEvaluator;
use function array_key_exists;

class ArrayAccessEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof ArrayAccessNode) {
            return null;
        }

        $arrayValue = $this->resolveArray($evaluator, $node, $params);
        $accessValue = $this->resolveAccess($evaluator, $node, $params);

        if (!array_key_exists($accessValue, $arrayValue)) {
            throw new ExpressionError(sprintf(
                'Array does not have key "%s", it has keys "%s"',
                get_class($arrayValue),
                implode('", "', array_keys($arrayValue))
            ));
        }

        return PhpValueFactory::fromValue($arrayValue[$accessValue]);
    }

    /**
     * @return scalar[]
     * @param parameters $params
     */
    private function resolveArray(Evaluator $evaluator, ArrayAccessNode $node, array $params): array
    {
        $value = $evaluator->evaluate($node->expression(), $params);
        
        if (!$value instanceof ListNode) {
            throw new ExpressionError(sprintf(
                'Array access expression on non-array, got "%s"',
                get_class($value)
            ));
        }

        return $value->value();
    }

    /**
     * @return int|string
     * @param parameters $params
     */
    private function resolveAccess(Evaluator $evaluator, ArrayAccessNode $node, array $params)
    {
        $accessValue = $evaluator->evaluate($node->access(), $params);
        
        if (!$accessValue instanceof ScalarValue) {
            throw new ExpressionError(sprintf(
                'Array access expression must evaluate to a scalar, got "%s"',
                get_class($accessValue)
            ));
        }

        $value = $accessValue->value();

        if (!is_string($value) && !is_integer($value)) {
            throw new ExpressionError(sprintf(
                'Array access expression must evaluate to either a string or an int, got "%s"',
                gettype($value)
            ));
        }

        return $value;
    }
}

