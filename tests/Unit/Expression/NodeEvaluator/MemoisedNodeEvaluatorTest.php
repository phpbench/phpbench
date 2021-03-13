<?php

namespace PhpBench\Tests\Unit\Expression\NodeEvaluator;

use PHPUnit\Framework\TestCase;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;
use PhpBench\Expression\NodeEvaluator\MemoisedNodeEvaluator;
use PhpBench\Tests\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class MemoisedNodeEvaluatorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy
     */
    private $inner;

    /**
     * @var MemoisedNodeEvaluator
     */
    private $nodeEvaluator;

    /**
     * @var Evaluator
     */
    private $evaluator;

    protected function setUp(): void
    {
        $this->inner = $this->prophesize(NodeEvaluator::class);

        $this->evaluator = $this->prophesize(Evaluator::class);
        $this->nodeEvaluator = new MemoisedNodeEvaluator($this->inner->reveal());
    }

    public function testEvaluateOnce(): void
    {
        $node = new StringNode("fo");
        $this->inner->evaluate($this->evaluator->reveal(), $node, [])->willReturn(new IntegerNode(1))->shouldBeCalledOnce();
        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, []);
        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, []);
        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, []);
        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, []);
    }
}
