<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class BooleanPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof BooleanNode) {
            return null;
        }

        return $node->value() ? 'true' : 'false';
    }
}
