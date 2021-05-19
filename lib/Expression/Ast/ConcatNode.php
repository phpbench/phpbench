<?php

namespace PhpBench\Expression\Ast;

class ConcatNode extends Node
{
    /**
     * @var Node
     */
    private $left;
    /**
     * @var Node
     */
    private $right;

    public function __construct(Node $left, Node $right)
    {
        $this->left = $left;
        $this->right = $right;
    }

    public function left(): Node
    {
        return $this->left;
    }

    public function right(): Node
    {
        return $this->right;
    }

    public function nodes()
    {
        $nodes = [$this->left()];

        if ($this->right instanceof ConcatNode) {
            $nodes = array_merge($nodes, $this->right->nodes());
        } else {
            $nodes[] = $this->right;
        }

        return $nodes;
    }
}
