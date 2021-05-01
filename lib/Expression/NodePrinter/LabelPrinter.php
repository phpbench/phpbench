<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\LabelNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class LabelPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof LabelNode) {
            return null;
        }

        return (string)$node->value();
    }
}
