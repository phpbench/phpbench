<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullSafeNode;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

use function array_reduce;

class ParameterPrinter implements NodePrinter
{
    /**
     */
    public function print(Printer $printer, Node $node): ?string
    {
        if (!$node instanceof ParameterNode) {
            return null;
        }

        return ltrim(array_reduce($node->segments(), function (string $carry, Node $segment) use ($printer) {
            if ($segment instanceof NullSafeNode) {
                $carry .= '?' . $printer->print($segment);

                return $carry;
            }

            $carry .= '.' . $printer->print($segment);

            return $carry;
        }, ''), '.');
    }
}
