<?php

namespace PhpBench\Expression\Ast;

class AccessNode extends Node
{
    public function __construct(private readonly Node $expression, private readonly Node $access)
    {
    }

    public function access(): Node
    {
        return $this->access;
    }

    public function expression(): Node
    {
        return $this->expression;
    }
}
