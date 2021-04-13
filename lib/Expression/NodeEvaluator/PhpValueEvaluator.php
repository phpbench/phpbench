<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;

class PhpValueEvaluator implements NodeEvaluator
{
    /**
     * {@inheritDoc}
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if ($node instanceof PhpValue) {
            return $node;
        }

        return null;
    }
}
