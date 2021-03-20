<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\ConcatenatedNode;
use PhpBench\Expression\Ast\ConcatNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
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
        return (function (PhpValue $left, PhpValue $right) {
            return new ConcatenatedNode(implode('', [
                (string)$left->value(),
                (string)$right->value()
            ]), $left, $right);
        })(
            $evaluator->evaluateType($node->left(), PhpValue::class, $params),
            $evaluator->evaluateType($node->right(), PhpValue::class, $params)
        );
    }
}
