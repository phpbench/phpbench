<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Exception\ExpressionError;
use PhpBench\Expression\NodeEvaluator;
use PhpBench\Expression\NodeEvaluators;

final class MainEvaluator implements Evaluator
{
    /**
     * @var NodeEvaluators
     */
    private $evaluators;

    public function __construct(NodeEvaluators $evaluators)
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
        return $this->evaluators->evaluateType($this, $node, $expectedType, $params);
    }

    /**
     * @param parameters $params
     */
    public function evaluate(Node $node, array $params): Node
    {
        return $this->evaluators->evaluate($this, $node, $params);
    }
}
