<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\AccessNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class ArrayAccessPrinter implements NodePrinter
{
    /**
     * {@inheritDoc}
     */
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof AccessNode) {
            return null;
        }

        return sprintf('%s[%s]', $printer->print($node->expression()), $printer->print($node->access()));
    }
}
