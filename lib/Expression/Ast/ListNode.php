<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Expression\Ast\PhpValueFactory;

final class ListNode extends DelimitedListNode
{
    /**
     * @param array<mixed> $values
     */
    public static function fromValues(array $values): self
    {
        return new self(array_map(function ($value) {
            if (is_array($value)) {
                return ListNode::fromValues($value);
            }

            return PhpValueFactory::fromNumber($value);
        }, $values));
    }
}
