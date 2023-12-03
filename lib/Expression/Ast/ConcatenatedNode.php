<?php

namespace PhpBench\Expression\Ast;

class ConcatenatedNode extends StringNode
{
    /**
     */
    public function __construct(string $string, private readonly Node $left, private readonly Node $right)
    {
        parent::__construct($string);
    }

    public function left(): Node
    {
        return $this->left;
    }

    public function right(): Node
    {
        return $this->right;
    }
}
