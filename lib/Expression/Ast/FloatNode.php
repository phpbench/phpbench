<?php

namespace PhpBench\Expression\Ast;

class FloatNode extends NumberNode
{
    public function __construct(private readonly float $number)
    {
    }

    public function value(): float
    {
        return $this->number;
    }
}
