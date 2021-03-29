<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class ComparisonPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof ComparisonNode) {
            return null;
        }

        return sprintf(
            '%s %s %s',
            $printer->print($node->left()),
            $node->operator(),
            $printer->print($node->right())
        );
    }
}
