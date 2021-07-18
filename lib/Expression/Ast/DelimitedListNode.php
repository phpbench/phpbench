<?php

namespace PhpBench\Expression\Ast;

abstract class DelimitedListNode extends PhpValue
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
    public function nodes(): array
    {
        return $this->nodes;
    }

    /**
     * @return mixed[]
     */
    public function value(): array
    {
        $values = [];

        foreach ($this->nodes as $key => $node) {
            assert($node instanceof PhpValue);
            $values[$key] = $node->value();
        }

        return $values;
    }

    /**
     * @return mixed[]
     */
    public function nonNullPhpValues(): array
    {
        return array_values(array_filter($this->value(), function ($value) {
            return $value !== null;
        }));
    }
}
