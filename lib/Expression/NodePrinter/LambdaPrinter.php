<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\CallableNode;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\LambdaNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class LambdaPrinter implements NodePrinter
{
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof LambdaNode) {
            return null;
        }

        return sprintf(
            '(%s) => %s',
            $printer->print($node->params()),
            $printer->print($node->expression())
        );
    }
}
