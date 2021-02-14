<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\DelimitedListNode;

abstract class DelimitedListNode implements Node
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
    public function expressions(): array
    {
        $exprs = [$this->left];

        if (!$this->right) {
            return $exprs;
        }
        if ($this->right instanceof DelimitedListNode) {
            $exprs = array_merge($exprs, $this->right->expressions());
        } else {
            $exprs[] = $this->right;
        }

        return $exprs;
    }
}
