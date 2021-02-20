<?php

namespace PhpBench\Expression\Ast;

abstract class DelimitedListNode implements Node, PhpValue
{
    /**
     * @var Node
     */
    private $left;

    /**
     * @var Node|null
     */
    private $right;

    public function __construct(Node $left, ?Node $right = null)
    {
        $this->left = $left;
        $this->right = $right;
    }

    public function left(): Node
    {
        return $this->left;
    }

    public function right(): ?Node
    {
        return $this->right;
    }

    /**
     * @return mixed[]
     */
    public function value(): array
    {
        $exprs = [$this->left];

        if (!$this->right) {
            return $exprs;
        }

        if ($this->right instanceof DelimitedListNode) {
            $exprs = array_merge($exprs, $this->right->value());
        } else {
            $exprs[] = $this->right;
        }

        return $exprs;
    }
}
