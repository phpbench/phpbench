<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

final class NormalizingPrinter implements Printer
{
    public function __construct(private readonly NodePrinter $printers)
    {
    }

    public function print(Node $node): string
    {
        return $this->printers->print($this, $node);
    }
}
