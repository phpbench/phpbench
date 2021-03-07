<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PercentDifferenceNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;
use PhpBench\Expression\NodePrinter\PercentageDifferencePrinter;

class PercentageDifferencePrinter implements NodePrinter
{
    /**
     * {@inheritDoc}
     */
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof PercentDifferenceNode) {
            return null;
        }

        $prefix = $node->percentage() > 0 ? '+' : '';

        return sprintf('%s%s%%', $prefix, $node->percentage());
    }
}
