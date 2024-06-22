<?php

namespace PhpBench\Expression\Ast;

class FunctionNode extends Node
{
    public function __construct(private readonly string $name, private readonly ?ArgumentListNode $arguments = null)
    {
    }

    public function args(): ?ArgumentListNode
    {
        return $this->arguments;
    }

    public function name(): string
    {
        return $this->name;
    }
}
