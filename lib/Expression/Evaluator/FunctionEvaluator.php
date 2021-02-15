<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\ExpressionFunctions;
use PhpBench\Expression\AbstractEvaluator;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<FunctionNode>
 */
class FunctionEvaluator extends AbstractEvaluator
{
    /**
     * @var ExpressionFunctions
     */
    private $functions;

    final public function __construct(ExpressionFunctions $functions)
    {
        $this->functions = $functions;
        parent::__construct(FunctionNode::class);
    }

    public function evaluate(Evaluator $evaluator, Node $node)
    {
        return $this->functions->execute($node->name(), array_map(function (Node $node) use ($evaluator) {
            return $evaluator->evaluate($node);
        }, $node->args()));
    }

}
