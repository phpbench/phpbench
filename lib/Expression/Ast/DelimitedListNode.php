<?php

namespace PhpBench\Expression\Ast;

abstract class DelimitedListNode implements Node, PhpValue
{
    /**
     * @var Node[]
     */
    private $nodes;

    /**
     * @param Node[] $nodes
     */
    public function __construct(array $nodes = [])
    {
        $this->nodes = $nodes;
    }

    /**
     * @return Node[]
     */
    public function value(): array
    {
        return $this->nodes;
    }

    /**
     * @return mixed[]
     */
    public function phpValues(): array
    {
        return array_map(function (PhpValue $node) {
            if ($node instanceof DelimitedListNode) {
                return $node->phpValues();
            }

            return $node->value();
        }, $this->nodes);
    }
}
