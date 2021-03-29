<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class UnitPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof UnitNode) {
            return null;
        }

        return $printer->print($node->unit());
    }
}
