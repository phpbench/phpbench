<?php

namespace PhpBench\Assertion\Ast;

class ThroughputValue implements Value
{
    /**
     * @var string
     */
    private $unit;

    /**
     * @var Value
     */
    private $value;

    public function __construct(Value $value, string $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function value(): Value
    {
        return $this->value;
    }

    public function unit(): string
    {
        return $this->unit;
    }
}
