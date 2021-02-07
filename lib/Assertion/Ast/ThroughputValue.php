<?php

namespace PhpBench\Assertion\Ast;

class ThroughputValue implements OperatorExpression
{
    /**
     * @var TimeUnitNode
     */
    private $unit;

    /**
     * @var ExpressionNode
     */
    private $value;

    public function __construct(ExpressionNode $value, TimeUnitNode $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function value(): ExpressionNode
    {
        return $this->value;
    }

    public function unit(): TimeUnitNode
    {
        return $this->unit;
    }
}
