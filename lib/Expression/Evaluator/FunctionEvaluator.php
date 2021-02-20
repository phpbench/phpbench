<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\NumberNodeFactory;
use PhpBench\Expression\Evaluator\AbstractEvaluator;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainEvaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\ExpressionFunctions;
use Throwable;

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

    public function evaluate(MainEvaluator $evaluator, Node $node): Node
    {
        try {
            return NumberNodeFactory::fromNumber($this->functions->execute($node->name(), array_map(function (Node $node) use ($evaluator) {
                return $evaluator->evaluate($node);
            }, $this->args($node->args()))));
        } catch (Throwable $throwable) {
            throw new EvaluationError(sprintf(
                'Call to function "%s" failed with error: %s',
                $node->name(),
                $throwable->getMessage()
            ));
        }
    }

    private function args(?ArgumentListNode $args)
    {
        if (null === $args) {
            return [];
        }

        return $args->expressions();
    }
}
