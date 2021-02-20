<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Ast\PercentageNode;
use PhpBench\Expression\Ast\TolerableNode;
use PhpBench\Expression\NormalizingPrinter;
use PhpBench\Expression\NodePrinter;

class PercentagePrinter implements NodePrinter
{
    public function print(NormalizingPrinter $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof PercentageNode) {
            return null;
        }

        return sprintf(
            '%s%%',
            $printer->print($node->value(), $params)
        );
    }
}
