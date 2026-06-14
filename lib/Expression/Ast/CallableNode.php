<?php

namespace PhpBench\Expression\Ast;

final class CallableNode extends Node
{
    public function __construct(private Node $callable, private ArgumentListNode $args)
    {
    }


    public function callable(): Node
    {
        return $this->callable;
    }

    public function args(): ArgumentListNode
    {
        return $this->args;
    }
}
