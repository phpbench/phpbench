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

    /**
     * @var string
     */
    private $asUnit;

    public function __construct(ExpressionNode $value, string $unit, ?string $asUnit = null)
    {
        $this->value = $value;
        $this->unit = $unit;
        $this->asUnit = $asUnit;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function value(): ExpressionNode
    {
        return $this->value;
    }

    public function asUnit(): string
    {
        return $this->asUnit ?: $this->unit;
    }
}
