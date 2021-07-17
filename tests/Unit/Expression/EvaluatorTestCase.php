<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Tests\IntegrationTestCase;

abstract class EvaluatorTestCase extends IntegrationTestCase
{
    /**
     * @param parameters $params
     */
    protected function evaluateNode(Node $node, array $params): Node
    {
        return $this->evaluator()->evaluate($node, $params);
    }

    protected function evaluator(): Evaluator
    {
        return $this->container()->get(
            Evaluator::class
        );
    }
}
