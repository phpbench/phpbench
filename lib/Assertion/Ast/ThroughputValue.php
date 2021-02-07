<?php

namespace PhpBench\Assertion\Ast;

class ThroughputValue implements OperatorExpression
{
    /**
     * @var UnitNode
     */
    private $unit;

    /**
     * @var ExpressionNode
     */
    private $value;

    public function __construct(ExpressionNode $value, UnitNode $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function value(): ExpressionNode
    {
        return $this->value;
    }

    public function unit(): UnitNode
    {
        return $this->unit;
    }
}
