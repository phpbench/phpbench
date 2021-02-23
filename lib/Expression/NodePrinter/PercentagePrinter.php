<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class PercentagePrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof PercentageNode) {
            return null;
        }

        return sprintf(
            '%s%%',
            $printer->print($node->value(), $params)
        );
    }
}
