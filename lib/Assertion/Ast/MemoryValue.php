<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Ast\ExpressionNode;

class MemoryValue implements NumberNode
{
    /**
     * @var Value
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
