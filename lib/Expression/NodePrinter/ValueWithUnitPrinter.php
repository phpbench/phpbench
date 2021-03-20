<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ValueWithUnitNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class ValueWithUnitPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof ValueWithUnitNode) {
            return null;
        }

        return sprintf(
            '%s %s',
            $printer->print($node->left(), $params),
            $printer->print($node->unit(), $params)
        );
    }
}
