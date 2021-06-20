<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;

class ArgumentListEvaluator implements NodeEvaluator
{
    /**
     * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof ArgumentListNode) {
            return null;
        }

        return new ArgumentListNode(
            array_map(function (Node $node) use ($evaluator, $params) {
                return $evaluator->evaluateType($node, PhpValue::class, $params);
            }, $node->nodes())
        );
    }
}
