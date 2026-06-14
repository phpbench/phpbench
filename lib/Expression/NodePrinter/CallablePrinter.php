<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\CallableNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class CallablePrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof CallableNode) {
            return null;
        }

        return sprintf(
            '(%s)(%s)',
            $printer->print($node->callable()),
            $printer->print($node->args())
        );
    }
}
