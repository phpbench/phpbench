<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\NodeEvaluator;

final class MainEvaluator implements Evaluator
{
    /**
     * @var NodeEvaluator
     */
    private $evaluators;

    /**
     */
    public function __construct(NodeEvaluator $evaluators)
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

        throw new EvaluationError($node, sprintf(
            'Expected "%s" but got "%s"',
            $expectedType,
            get_class($evaluated)
        ));
    }

    /**
     * @param parameters $params
     */
    public function evaluate(Node $node, array $params): Node
    {
        return $this->evaluators->evaluate($this, $node, $params);
    }
}
