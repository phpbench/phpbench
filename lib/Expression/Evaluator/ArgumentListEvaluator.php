<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\Node;
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

    public function evaluate(Evaluator $evaluator, Node $node): Node
    {
        return new ArgumentListNode($evaluator->evaluate($node->left()), $evaluator->evaluate($node->right()));
    }
}
