<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;

class StringEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof StringNode) {
            return null;
        }

        return $node;
    }
}
