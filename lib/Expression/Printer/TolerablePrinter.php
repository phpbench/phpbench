<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\MainPrinter;
use PhpBench\Expression\NodePrinter;

class TolerablePrinter implements NodePrinter
{
    public function print(MainPrinter $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof TolerableNode) {
            return null;
        }

        return sprintf(
            '%s +/- %s',
            $printer->print($node->value(), $params),
            $printer->print($node->tolerance(), $params)
        );
    }
}
