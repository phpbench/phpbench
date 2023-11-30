<?php

namespace PhpBench\Expression\Ast;

class IntegerNode extends NumberNode
{
    public function __construct(private readonly int $value)
    {
    }

    public function value(): int
    {
        return $this->value;
    }
}
