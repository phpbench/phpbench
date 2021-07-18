<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullSafeNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
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

        throw new EvaluationError(
            $node,
            'Null safe operator can only be used before an access expression'
        );
    }
}
