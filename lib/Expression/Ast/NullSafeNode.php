<?php

namespace PhpBench\Expression\Ast;

class NullSafeNode extends Node
{
    public function __construct(private readonly Node $variable)
    {
    }

    public function node(): Node
    {
        return $this->variable;
    }
}
