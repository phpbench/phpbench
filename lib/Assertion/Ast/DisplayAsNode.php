<?php

namespace PhpBench\Assertion\Ast;

class DisplayAsNode implements ExpressionNode
{
    /**
     * @var ExpressionNode
     */
    private $node;
    /**
     * @var UnitNode
     */
    private $unit;

    public function __construct(ExpressionNode $node, UnitNode $unit)
    {
        $this->node = $node;
        $this->unit = $unit;
    }

    public function node(): ExpressionNode
    {
        return $this->node;
    }

    public function unit(): UnitNode
    {
        return $this->unit;
    }
}
