<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class ParameterPrinter implements NodePrinter
{
    /**
     */
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof ParameterNode) {
            return null;
        }

        return implode('.', $node->segments());
    }
}
