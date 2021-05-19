<?php

namespace PhpBench\Expression\Ast;

abstract class BinaryOperatorNode extends Node
{
    /**
     * @var Node
     */
    private $left;
    /**
     * @var string
     */
    private $operator;
    /**
     * @var Node
     */
    private $right;

    public function __construct(Node $left, string $operator, Node $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
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
