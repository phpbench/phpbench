<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;

final class UnderlinePrinterFactory
{
    public function __construct(private readonly NodePrinter $printers)
    {
    }

    public function underline(Node $targetNode): UnderlinePrinter
    {
        return new UnderlinePrinter($this->printers, $targetNode);
    }
}
