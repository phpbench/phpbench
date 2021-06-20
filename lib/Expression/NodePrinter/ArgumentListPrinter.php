<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class ArgumentListPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof ArgumentListNode) {
            return null;
        }

        $out = [];

        foreach ($node->nodes() as $expression) {
            $out[] = $printer->print($expression);
        }

        return implode(', ', $out);
    }
}
