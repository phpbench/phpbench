<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class VariablePrinter implements NodePrinter
{
    /**
     * {@inheritDoc}
     */
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof VariableNode) {
            return null;
        }

        return $node->name();
    }
}
