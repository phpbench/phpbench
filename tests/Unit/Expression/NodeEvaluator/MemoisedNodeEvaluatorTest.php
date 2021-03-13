<?php

namespace PhpBench\Tests\Unit\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;
use PhpBench\Expression\NodeEvaluator\MemoisedNodeEvaluator;
use PhpBench\Tests\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class MemoisedNodeEvaluatorTest extends TestCase
{
    use ProphecyTrait;

    private $inner;
    private $evaluator;

    /**
     * @var MemoisedNodeEvaluator
     */
    private $nodeEvaluator;


    protected function setUp(): void
    {
        $this->inner = $this->prophesize(NodeEvaluator::class);

        $this->evaluator = $this->prophesize(Evaluator::class);
        $this->nodeEvaluator = new MemoisedNodeEvaluator($this->inner->reveal());
    }

    public function testEvaluateOnce(): void
    {
        $node = new StringNode("fo");
        $this->inner->evaluate(
            $this->evaluator->reveal(),
            $node,
            []
        )->willReturn(new IntegerNode(1))->shouldBeCalledOnce();

        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, []);
        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, []);
        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, []);
        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, []);
    }

    public function testVariesWithParameters(): void
    {
        $node = new StringNode("fo");

        $this->inner->evaluate($this->evaluator->reveal(), $node, Argument::any())->willReturn(
            new IntegerNode(1),
            new IntegerNode(1)
        )->shouldBeCalledTimes(2);

        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, []);
        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, ['foo' => 'bar']);
        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, []);
        $this->nodeEvaluator->evaluate($this->evaluator->reveal(), $node, []);
    }
}
