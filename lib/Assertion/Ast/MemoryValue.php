<?php

namespace PhpBench\Assertion\Ast;

class MemoryValue implements Value
{
    /**
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    public function __construct(float $value, string $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function toBytes(): float
    {
        return $this->value;
    }
}
