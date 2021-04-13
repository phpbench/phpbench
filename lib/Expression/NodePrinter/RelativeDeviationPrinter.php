<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\RelativeDeviationNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class RelativeDeviationPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof RelativeDeviationNode) {
            return null;
        }

        return sprintf(
            '%s%.2f%%',
            $printer->print(new UnitNode(new StringNode('±'))),
            $node->value()
        );
    }
}
