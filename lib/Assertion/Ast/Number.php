<?php

namespace PhpBench\Assertion\Ast;

class Number
{
    private $number;

    public function __construct($number)
    {
        $this->number = $number;
    }

    public function lessThanOrEqualTo(Number $number): bool
    {
        return $this->number <= $number->number;
    }

    public function number()
    {
        return $this->number;
    }

    public function asPositive(): Number
    {
        if ($this->number < 0) {
            return new self($this->number * -1);
        }

        return $this;
    }
}
