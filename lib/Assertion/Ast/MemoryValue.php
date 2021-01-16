<?php

namespace PhpBench\Assertion\Ast;

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

    public function __construct(Value $value, string $unit, ?string $asUnit = null)
    {
        $this->value = $value;
        $this->unit = $unit;
        $this->asUnit = $asUnit;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function value(): Value
    {
        return $this->value;
    }

    public function asUnit(): string
    {
        return $this->asUnit ?: $this->unit;
    }
}
