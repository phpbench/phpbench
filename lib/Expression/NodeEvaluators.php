<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator\MainEvaluator;
use PhpBench\Expression\Evaluator\parameters;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Exception\EvaluatorError;
use PhpBench\Expression\Exception\ExpressionError;

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

    /**
     * @param parameters $params
     */
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

    /**
     * @template T of Node
     *
     * @param class-string<T> $expectedType
     * @param parameters $params
     *
     * @return T
     */
    public function evaluateType(Evaluator $evaluator, Node $node, string $expectedType, array $params): Node
    {
        $evaluated = $this->evaluate($evaluator, $node, $params);

        if ($evaluated instanceof $expectedType) {
            return $evaluated;
        }

        throw new EvaluationError($node, sprintf(
            'Expected "%s" but got "%s"', $expectedType, get_class($node)
        ));
    }
}

