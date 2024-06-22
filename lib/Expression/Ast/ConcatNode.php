<?php

namespace PhpBench\Expression\Ast;

class ConcatNode extends Node
{
    public function __construct(private readonly Node $left, private Node $right)
    {
    }

    public function left(): Node
    {
        return $this->left;
    }

    public function right(): Node
    {
        return $this->right;
    }

    /**
     * @return list<Node>
     */
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
