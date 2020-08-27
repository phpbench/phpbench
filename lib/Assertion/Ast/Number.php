<?php

namespace PhpBench\Assertion\Ast;

class Number
{
    private $number;

    public function __construct($number)
    {
        $this->number = $number;
    }
}
