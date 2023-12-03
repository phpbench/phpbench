<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator\parameters;
use PhpBench\Expression\Exception\EvaluationError;

final class NodeEvaluators implements NodeEvaluator
{
    /**
     * @param NodeEvaluator[] $evaluators
     */
    public function __construct(private readonly array $evaluators)
    {
    }

    /**
     * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        foreach ($this->evaluators as $nodeEvaluator) {
            $evaluated = $nodeEvaluator->evaluate($evaluator, $node, $params);

            if (null === $evaluated) {
                continue;
            }

            return $evaluated;
        }

        throw new EvaluationError($node, sprintf(
            'Could not find evaluator for node of type "%s"',
            $node::class
        ));
    }
}
