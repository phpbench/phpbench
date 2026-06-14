<?php

namespace PhpBench\Expression\Ast;

final class LambdaNode extends Node
{
    public function __construct(private ArgumentListNode $node, private Node $expression)
    {
    }

}
