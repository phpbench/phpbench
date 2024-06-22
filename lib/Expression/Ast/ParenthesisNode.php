<?php

namespace PhpBench\Expression\Ast;

final class ParenthesisNode extends Node
{
    public function __construct(private readonly Node $expression)
    {
    }

    public function expression(): Node
    {
        return $this->expression;
    }
}
