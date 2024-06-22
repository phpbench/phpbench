<?php

namespace PhpBench\Expression\Ast;

final class TolerableNode extends Node
{
    public function __construct(private readonly Node $value, private readonly Node $tolerance)
    {
    }

    public function tolerance(): Node
    {
        return $this->tolerance;
    }

    public function value(): Node
    {
        return $this->value;
    }
}
