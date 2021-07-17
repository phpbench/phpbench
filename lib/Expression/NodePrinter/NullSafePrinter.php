<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullSafeNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class NullSafePrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof NullSafeNode) {
            return null;
        }

        return $printer->print($node->node()) . '?';
    }
}
