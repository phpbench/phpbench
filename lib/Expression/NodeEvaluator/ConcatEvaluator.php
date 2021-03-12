<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\ConcatNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<ConcatNode>
 */
class ConcatEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ConcatNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        return new StringNode(implode('', [
            (string)$evaluator->evaluateType($node->left(), PhpValue::class, $params)->value(),
            (string)$evaluator->evaluateType($node->right(), PhpValue::class, $params)->value()
        ]));
    }
}
