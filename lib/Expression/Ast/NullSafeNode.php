<?php

namespace PhpBench\Expression\Ast;

class NullSafeNode extends Node
{
    /**
     * @var Node
     */
    private $variable;

    public function __construct(Node $variable)
    {
        $this->variable = $variable;
    }

    public function variable(): Node
    {
        return $this->variable;
    }
}
