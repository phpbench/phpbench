<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<ArgumentListNode>
 */
class ArgumentListEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(ArgumentListNode::class);
    }

    /**
     * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        return new ArgumentListNode(
            array_map(function (Node $node) use ($evaluator, $params) {
                return $evaluator->evaluateType($node, PhpValue::class, $params);
            }, $node->value())
        );
    }
}
