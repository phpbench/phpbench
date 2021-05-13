<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PropertyAccessNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class ParameterPrinter implements NodePrinter
{
    /**
     */
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof PropertyAccessNode) {
            return null;
        }

        return implode('.', array_map(function (Node $node) use ($printer) {
            return $printer->print($node);
        }, $node->segments()));
    }
}
