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
}
