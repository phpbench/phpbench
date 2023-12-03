<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

final class UnderlinePrinter implements Printer
{
    public function __construct(private readonly NodePrinter $printers, private readonly Node $targetNode)
    {
    }

    public function print(Node $node): string
    {
        $printed = $this->printers->print($this, $node);

        if ($node !== $this->targetNode) {
            return preg_replace('{[^-]}', ' ', $printed);
        }

        return str_repeat('-', mb_strlen($printed));
    }
}
