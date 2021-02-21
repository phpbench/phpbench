<?php

namespace PhpBench\Expression\Ast;

use Exception;

final class ListNode extends DelimitedListNode
{
    /**
     * @param array<mixed> $values
     */
    public static function fromValues(array $values): self
    {
        $node = null;
        $left = array_shift($values);
        if (null === $left) {
            return new ListNode();
        }
        $left = NumberNodeFactory::fromNumber($left);
        while ($values) {
            $right = array_shift($values);
            $right = NumberNodeFactory::fromNumber($right);
            $node = new ListNode($left, $right);
            $left = $node;
        }
        if ($node === null) {
            return new ListNode($left);
        }
        return $left;
    }
}
