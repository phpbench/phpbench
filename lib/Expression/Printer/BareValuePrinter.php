<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\DelimitedListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Printer;

/**
 * Returns the bare, undecorated PHP values of the _given_ node if applicable.
 */
class BareValuePrinter implements Printer
{
    /**
     * {@inheritDoc}
     */
    public function print(Node $node): string
    {
        if (!$node instanceof PhpValue) {
            return '??';
        }

        if ($node instanceof DelimitedListNode) {
            return sprintf('[%s]', implode(', ', array_map(function (Node $node) {
                return $this->print($node);
            }, $node->nodes())));
        }

        return (string)$node->value();
    }
}
