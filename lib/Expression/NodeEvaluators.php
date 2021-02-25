<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator\MainEvaluator;
use PhpBench\Expression\Evaluator\parameters;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Exception\EvaluatorError;

final class NodeEvaluators
{
    /**
     * @var NodeEvaluator[]
     */
    private $evaluators;

    /**
     * @param NodeEvaluator[] $evaluators
     */
    public function __construct(array $evaluators)
    {
        $this->evaluators = $evaluators;
    }

    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        foreach ($this->evaluators as $nodeEvaluator) {
            if (!$nodeEvaluator->evaluates($node)) {
                continue;
            }

            $evaluated = $nodeEvaluator->evaluate($evaluator, $node, $params);

            return $evaluated;
        }

        throw new EvaluationError($node, sprintf(
            'Could not find evaluator for node of type "%s"', get_class($node)
        ));
    }
}

