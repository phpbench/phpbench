<?php

namespace PhpBench\Expression\Ast;

abstract class DelimitedListNode extends PhpValue
{
    /**
     * @param Node[] $nodes
     */
    public function __construct(private readonly array $nodes = [])
    {
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
