<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Ast\ExpressionNode;

class ThroughputValue implements ExpressionNode
{
    /**
     * @var UnitNOde
     */
    private $unit;

    /**
     * @var Value
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
