<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\NullSafeNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Exception\VariableNotFound;
use PhpBench\Expression\NodeEvaluator;

class NullSafeEvaluator implements NodeEvaluator
{
    /**
     * {@inheritDoc}
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof NullSafeNode) {
            return null;
        }

        if ($node->node() instanceof VariableNode) {
            try {
                $value = $evaluator->evaluate($node->node(), $params);
            } catch (VariableNotFound $e) {
                return new NullNode();
            }

            return $value;
        }

        throw new EvaluationError(
            $node,
            'Null safe operator can only be used on variables or access expressions'
        );
    }
}
