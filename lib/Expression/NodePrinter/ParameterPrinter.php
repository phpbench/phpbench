<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class ParameterPrinter implements NodePrinter
{
    /**
     * @param parameters $params
     */
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof ParameterNode) {
            return null;
        }

        return implode('.', $node->segments());
    }
}
