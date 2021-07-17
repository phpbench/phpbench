<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;
use PhpBench\Expression\NodeEvaluators;
use PhpBench\Tests\IntegrationTestCase;

class NodeEvaluatorsTest extends IntegrationTestCase
{
    /**
     * @var NodeEvaluators<Node>
     */
    private $evaluators;

    /**
     * @var NodeEvaluatorsTest
     */
    private $evaluator;


    protected function setUp(): void
    {
        $this->evaluators = $this->container()->get(NodeEvaluator::class);
        $this->evaluator = $this->container()->get(Evaluator::class);
    }

    public function testEvaluate(): void
    {
        self::assertEquals(new IntegerNode(1), $this->evaluators->evaluate(
            $this->evaluator,
            new VariableNode('one'),
            [
                'one' => 1,
            ]
        ));
    }
}
