<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
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
        $this->evaluators = $this->container()->get(NodeEvaluators::class);
        $this->evaluator = $this->container()->get(Evaluator::class);
    }

    public function testErrorOnUnExpectedType(): void
    {
        $this->expectException(EvaluationError::class);
        $this->expectExceptionMessage('IntegerNode');
        $this->evaluators->evaluateType(
            $this->evaluator,
            new ParameterNode(['one']),
            PercentageNode::class,
            [
                'one' => 1,
            ]
        );
    }
}
