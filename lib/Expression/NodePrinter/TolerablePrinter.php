<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\Ast\ToleratedTrue;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class TolerablePrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if ($node instanceof ToleratedTrue) {
            return '~true';
        }

        if (
            !$node instanceof TolerableNode
        ) {
            return null;
        }

        return sprintf(
            '%s ± %s',
            $printer->print($node->value()),
            $printer->print($node->tolerance())
        );
    }
}
