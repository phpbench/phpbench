<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class StringPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof StringNode) {
            return null;
        }

        return (string)$node->value();
    }
}
