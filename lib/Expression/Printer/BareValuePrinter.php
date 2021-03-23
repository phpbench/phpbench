<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\DelimitedListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Printer;

class BareValuePrinter implements Printer
{
    /**
     * {@inheritDoc}
     */
    public function print(Node $node, array $params): string
    {
        if (!$node instanceof PhpValue) {
            return 'n/a';
        }

        if ($node instanceof DelimitedListNode) {
            return implode(', ', array_map(function (Node $node) use ($params) {
                return $this->print($node, $params);
            }, $node->value()));
        }

        return $node->value();
    }
}
