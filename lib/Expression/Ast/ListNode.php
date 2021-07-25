<?php

namespace PhpBench\Expression\Ast;

/**
 * Optimisation note: When `fromValues` is used, the source of truth is
 * switched from a list of nodes to a list of primitive values. This avoids the
 * overhead of converting nodes back to values and improves performance
 * significantly.
 */
final class ListNode extends DelimitedListNode
{
    /**
     * @var mixed[]
     */
    private $values;

    /**
     * @param array<mixed> $values
     */
    public static function fromValues(array $values): self
    {
        $new = new self([]);
        $new->values = $values;

        return $new;
    }

    /**
     * @return Node[]
     */
    public function nodes(): array
    {
        if (null === $this->values) {
            return parent::nodes();
        }

        return array_map(function ($v) {
            return PhpValueFactory::fromValue($v);
        }, $this->values);
    }

    public function value(): array
    {
        if (null === $this->values) {
            return parent::value();
        }

        return $this->values;
    }

    /**
     * @return mixed[]
     */
    public function nonNullPhpValues(): array
    {
        if (null === $this->values) {
            return parent::nonNullPhpValues();
        }

        return array_values(array_filter($this->values, function ($value) {
            return $value !== null;
        }));
    }

    /**
     * Return a vanilla node list (for comparisons in tests)
     */
    public function toNodeList(): self
    {
        return new self($this->nodes());
    }
}
