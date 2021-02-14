<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

final class Evaluator
{
    /**
     * @var AbstractEvaluator<Node>[]
     */
    private $evaluators;

    /**
     * @param AbstractEvaluator<Node>[] $evaluators
     */
    public function __construct(array $evaluators)
    {
        $this->evaluators = $evaluators;
    }

    /**
     * @return mixed
     */
    public function evaluate(Node $node)
    {
        foreach ($this->evaluators as $evaluator) {
            if ($evaluator->evaluates($node)) {
                return $evaluator->evaluate($this, $node);
            }
        }

        return null;
    }
}
