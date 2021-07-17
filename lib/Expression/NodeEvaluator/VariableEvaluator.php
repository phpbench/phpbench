<?php

namespace PhpBench\Expression\NodeEvaluator;

use ArrayAccess;
use PhpBench\Data\DataFrame;
use PhpBench\Data\Row;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\NullSafeNode;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Exception\KeyDoesNotExist;
use PhpBench\Expression\NodeEvaluator;

class VariableEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof VariableNode) {
            return null;
        }

        return PhpValueFactory::fromValue($this->resolveFromParameters($node->name(), $params, $node));
    }

    /**
     * @return mixed
     * @param parameters $params
     */
    private function resolveFromParameters(string $key, array $params, VariableNode $node)
    {
        if (!isset($params[$key])) {
            throw new EvaluationError(
                $node,
                sprintf(
                    'Variable "%s" not found, known variables: "%s"',
                    $key,
                    implode('", "', array_keys($params))
                )
            );
        }

        return $params[$key];
    }
}
