<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Exception\ExpressionError;

final class Evaluator
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
     *
     * @return T
     */
    public function evaluateType(Node $node, string $expectedType): Node
    {
        $evaluated = $this->evaluate($node);

        if ($evaluated instanceof $expectedType) {
            return $evaluated;
        }

        throw new ExpressionError(sprintf(
            'Expected "%s" but got "%s"', $expectedType, get_class($node)
        ));
    }

    /**
     */
    public function evaluate(Node $node): Node
    {
        foreach ($this->evaluators as $evaluator) {
            if (!$evaluator->evaluates($node)) {
                continue;
            }
            $evaluated = $evaluator->evaluate($this, $node);

            return $evaluated;
        }

        throw new EvaluationError(sprintf(
            'Could not find evaluator for node of type "%s"', get_class($node)
        ));
    }
}
