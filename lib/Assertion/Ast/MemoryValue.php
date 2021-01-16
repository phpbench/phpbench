<?php

namespace PhpBench\Assertion\Ast;

class MemoryValue implements Value
{
    /**
     * @var NumberNode
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    public function __construct(NumberNode $value, string $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function value(): NumberNode
    {
        return $this->value;
    }
}
