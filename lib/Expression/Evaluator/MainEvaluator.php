<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Exception\ExpressionError;
use PhpBench\Expression\NodeEvaluator;

final class MainEvaluator implements Evaluator
{
    /**
     * @var NodeEvaluator<Node>[]
     */
    private $evaluators;

    /**
     * @param NodeEvaluator<Node>[] $evaluators
     */
    public function __construct(array $evaluators)
    {
        $this->evaluators = $evaluators;
    }

    /**
     * @template T of Node
     *
     * @param class-string<T> $expectedType
     * @param parameters $params
     *
     * @return T
     */
    public function evaluateType(Node $node, string $expectedType, array $params): Node
    {
        $evaluated = $this->evaluate($node, $params);

        if ($evaluated instanceof $expectedType) {
            return $evaluated;
        }

        throw new ExpressionError(sprintf(
            'Expected "%s" but got "%s"', $expectedType, get_class($node)
        ));
    }

    /**
     * @param parameters $params
     */
    public function evaluate(Node $node, array $params): Node
    {
        foreach ($this->evaluators as $evaluator) {
            if (!$evaluator->evaluates($node)) {
                continue;
            }
            $evaluated = $evaluator->evaluate($this, $node, $params);

            return $evaluated;
        }

        throw new EvaluationError($node, sprintf(
            'Could not find evaluator for node of type "%s"', get_class($node)
        ));
    }
}

