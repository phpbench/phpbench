<?php

namespace PhpBench\Assertion\Ast;

class Value extends Parameter
{
    /**
     * @var Number
     */
    private $number;

    /**
     * @var Unit
     */
    private $unit;

    public function __construct(Number $number, Unit $unit)
    {
        $this->number = $number;
        $this->unit = $unit;
    }

    public function unit(): Unit
    {
        return $this->unit;
    }

    public function number(): Number
    {
        return $this->number;
    }

    public function resolveValue(Arguments $arguments)
    {
        return $this->number->number();
    }
}
