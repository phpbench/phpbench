<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;

class NullEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof NullNode) {
            return null;
        }

        return $node;
    }
}
