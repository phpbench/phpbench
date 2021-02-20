<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainPrinter;
use PhpBench\Expression\NodePrinter;

class ListPrinter implements NodePrinter
{
    public function print(MainPrinter $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof ListNode) {
            return null;
        }

        $out = [];
        foreach ($node->expressions() as $expression) {
            $out[] = $printer->print($expression, $params);
        }

        return '[' . implode(', ', $out) . ']';
    }
}
