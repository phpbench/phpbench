<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class ParenthesisPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof ParenthesisNode) {
            return null;
        }

        return sprintf('(%s)', $printer->print($node->expression()));
    }
}
