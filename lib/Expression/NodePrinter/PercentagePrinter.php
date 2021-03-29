<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class PercentagePrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof PercentageNode) {
            return null;
        }

        return sprintf(
            '%.2f%s',
            $printer->print($node->valueNode()),
            $printer->print(new UnitNode(new StringNode('%')))
        );
    }
}
