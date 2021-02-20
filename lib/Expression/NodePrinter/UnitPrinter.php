<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class UnitPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof UnitNode) {
            return null;
        }

        return sprintf(
            '%s %s',
            $printer->print($node->left(), $params),
            $node->unit()
        );
    }
}
