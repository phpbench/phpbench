<?php

namespace PhpBench\Expression\Ast;

class AccessNode extends Node
{
    /**
     * @var Node
     */
    private $expression;
    /**
     * @var Node
     */
    private $access;

    public function __construct(Node $expression, Node $access)
    {
        $this->expression = $expression;
        $this->access = $access;
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
