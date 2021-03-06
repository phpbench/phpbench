<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\ConcatNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class ConcatPrinter implements NodePrinter
{
    /**
     * {@inheritDoc}
     */
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof ConcatNode) {
            return null;
        }

        return implode('', [
            trim($printer->print($node->left(), $params), '"'),
            trim($printer->print($node->right(), $params), '"')
        ]);
    }
}
