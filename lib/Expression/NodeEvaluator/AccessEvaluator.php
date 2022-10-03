<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\AccessNode;
use PhpBench\Expression\Ast\DataFrameNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\NullSafeNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\ScalarValue;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\ExpressionError;
use PhpBench\Expression\NodeEvaluator;

use function array_key_exists;

class AccessEvaluator implements NodeEvaluator
{
    /**
     * @var DataFrameEvaluator
     */
    private $dataFrameEvaluator;

    public function __construct()
    {
        $this->dataFrameEvaluator = new DataFrameEvaluator();
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof AccessNode) {
            return null;
        }

        $nullSafe = false;
        $container = $node->expression();

        if ($container instanceof NullSafeNode) {
            $nullSafe = true;
            $container = $container->node();
        }

        $value = $evaluator->evaluate($container, $params);

        if ($value instanceof DataFrameNode) {
            return $this->dataFrameEvaluator->evaluate($evaluator, $value, $node->access(), $params, $nullSafe);
        }

        $accessValue = $this->resolveAccess($evaluator, $node, $params);

        try {
            $arrayValue = $this->resolveArray($evaluator, $value);
        } catch (\Exception $e) {
            throw new ExpressionError(sprintf(
                'Could not get value for key "%s": %s',
                $accessValue,
                $e->getMessage()
            ));
        }

        if (!array_key_exists($accessValue, $arrayValue)) {
            if ($nullSafe === true) {
                return new NullNode();
            }

            throw new ExpressionError(sprintf(
                'Array does not have key "%s", it has keys "%s"',
                $accessValue,
                implode('", "', array_keys($arrayValue))
            ));
        }

        return PhpValueFactory::fromValue($arrayValue[$accessValue]);
    }

    /**
     * @return scalar[]
     */
    private function resolveArray(Evaluator $evaluator, Node $value): array
    {
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
     *
     * @param parameters $params
     */
    private function resolveAccess(Evaluator $evaluator, AccessNode $node, array $params)
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
