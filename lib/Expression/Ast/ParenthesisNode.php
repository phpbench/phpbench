<?php

namespace PhpBench\Expression\Ast;

final class ParenthesisNode extends Node
{
    /**
     * @var Node
     */
    private $expression;

    public function __construct(Node $expression)
    {
        $this->expression = $expression;
    }

    public function expression(): Node
    {
        return $this->expression;
    }
}
