<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Ast\ExpressionNode;

class ThroughputValue implements ExpressionNode
{
    /**
     * @var string
     */
    private $unit;

    /**
     * @var Value
     */
    private $value;

    public function __construct(ExpressionNode $value, string $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function value(): ExpressionNode
    {
        return $this->value;
    }

    public function unit(): string
    {
        return $this->unit;
    }
}
