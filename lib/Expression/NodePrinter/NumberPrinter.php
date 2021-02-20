<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class NumberPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof NumberNode) {
            return null;
        }

        return (string)$node->value();
    }
}
