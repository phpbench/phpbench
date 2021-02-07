<?php

namespace PhpBench\Assertion\Ast;

class MemoryValue implements NumberNode
{
    /**
     * @var ExpressionNode
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    public function __construct(ExpressionNode $value, string $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function value(): ExpressionNode
    {
        return $this->value;
    }
}
