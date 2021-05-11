<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\UnrepresentableValueNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class UnrepresentableValuePrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof UnrepresentableValueNode) {
            return null;
        }

        return sprintf('<%s>', gettype($node->value()));
    }
}
