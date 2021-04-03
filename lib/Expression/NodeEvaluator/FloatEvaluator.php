<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;

class FloatEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof FloatNode) {
            return null;
        }

        return $node;
    }
}
