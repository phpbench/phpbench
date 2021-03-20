<?php

namespace PhpBench\Expression\Ast;

class ConcatenatedNode extends StringNode
{
    /**
     * @var Node
     */
    private $left;
    /**
     * @var Node
     */
    private $right;

    /**
     */
    public function __construct(string $string, Node $left, Node $right)
    {
        parent::__construct($string);
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
}
