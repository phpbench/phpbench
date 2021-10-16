<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\DataFrameNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class DataFramePrinter implements NodePrinter
{
    /**
     * {@inheritDoc}
     */
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof DataFrameNode) {
            return null;
        }

        return sprintf('[frame cols=%s rows=%d]', count($node->dataFrame()->rows()), count($node->dataFrame()->columnNames()));
    }
}
