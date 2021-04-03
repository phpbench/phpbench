<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;

class IntegerEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof IntegerNode) {
            return null;
        }

        return $node;
    }
}
