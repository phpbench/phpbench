<?php

namespace PhpBench\Expression\Evaluator;

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
            $evaluator->evaluateType($node->left(), PhpValue::class, $params),
            $evaluator->evaluateType($node->right(), PhpValue::class, $params)
        );
    }
}
