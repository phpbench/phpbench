<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class NullPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof NullNode) {
            return null;
        }

        return 'null';
    }
}
