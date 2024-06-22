<?php

namespace PhpBench\Expression\Ast;

final class VariableNode extends Node
{
    public function __construct(private readonly string $name)
    {
    }

    public function name(): string
    {
        return $this->name;
    }
}
