<?php

namespace PhpBench\Expression\Ast;

final class ListNode extends DelimitedListNode
{
    /**
     * @param array<mixed> $values
     */
    public static function fromValues(array $values): self
    {
        $listValues = [];

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $listValues[$key] = ListNode::fromValues($value);
            }
            $listValues[$key] = PhpValueFactory::fromValue($value);
        }

        return new self($listValues);
    }
}
