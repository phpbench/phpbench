<?php

namespace PhpBench\Expression\Ast;

abstract class BinaryOperatorNode extends Node
{
    public function __construct(private readonly Node $left, private readonly string $operator, private readonly Node $right)
    {
    }

    public function left(): Node
    {
        return $this->left;
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function right(): Node
    {
        return $this->right;
    }
}
