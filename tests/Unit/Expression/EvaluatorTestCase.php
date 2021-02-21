<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Tests\IntegrationTestCase;

abstract class EvaluatorTestCase extends IntegrationTestCase
{
    protected function evaluateNode(Node $node, array $params): Node
    {
        $container = $this->container();

        return $container->get(
            Evaluator::class
        )->evaluate($node, $params);
    }
}
