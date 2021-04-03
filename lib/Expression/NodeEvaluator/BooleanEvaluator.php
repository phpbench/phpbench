<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;

class BooleanEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof BooleanNode) {
            return null;
        }

        return $node;
    }
}
