<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\ArrayAccessNode;
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
        if (!$node instanceof ArrayAccessNode) {
            return null;
        }

        return sprintf('%s[%s]', $printer->print($node->expression()), $printer->print($node->access()));
    }
}
