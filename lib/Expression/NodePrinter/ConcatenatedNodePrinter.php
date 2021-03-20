<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\ConcatenatedNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class ConcatenatedNodePrinter implements NodePrinter
{
    /**
     * {@inheritDoc}
     */
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof ConcatenatedNode) {
            return null;
        }

        return implode('', [
            $printer->print($node->left(), $params),
            $printer->print($node->right(), $params)
        ]);
    }
}
