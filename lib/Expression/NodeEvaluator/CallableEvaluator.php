<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\CallableNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ScalarValue;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\NodeEvaluator;

class CallableEvaluator implements NodeEvaluator
{
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof CallableNode) {
            return null;
        }
        $args = array_map(function (Node $node) use ($evaluator, $params) {
            $value = $evaluator->evaluate($node, $params);

            if (!$value instanceof ScalarValue) {
                throw new EvaluationError($node, sprintf(
                    'Expected expression to resolve to a scalar value, got %s',
                    $value::class
                ));
            }

            return $value->value();
        }, $node->args()->nodes());

        return $evaluator->evaluate($node->callable(), $args);
    }
}
