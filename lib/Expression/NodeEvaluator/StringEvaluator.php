<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<StringNode>
 */
class StringEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(StringNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        return $node;
    }
}
