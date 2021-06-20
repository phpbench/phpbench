<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class ListPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof ListNode) {
            return null;
        }

        $out = [];

        foreach ($node->nodes() as $expression) {
            $out[] = $printer->print($expression);
        }

        return '[' . implode(', ', $out) . ']';
    }
}
