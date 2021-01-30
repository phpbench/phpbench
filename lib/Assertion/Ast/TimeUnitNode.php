<?php

namespace PhpBench\Assertion\Ast;

class TimeUnitNode implements UnitNode
{
    /**
     * @var string
     */
    private $unit;

    public function __construct(string $unit)
    {
        $this->unit = $unit;
    }

    public function unit(): string
    {
        return $this->unit;
    }
}
