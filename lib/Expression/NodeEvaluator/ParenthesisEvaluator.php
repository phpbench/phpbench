<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;

class ParenthesisEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof ParenthesisNode) {
            return null;
        }

        return $evaluator->evaluate($node->expression(), $params);
    }
}
