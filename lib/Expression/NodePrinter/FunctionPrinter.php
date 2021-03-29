<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class FunctionPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof FunctionNode) {
            return null;
        }

        return sprintf('%s(%s)', $node->name(), $printer->print($node->args()));
    }
}
