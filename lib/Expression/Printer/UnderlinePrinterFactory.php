<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;

final class UnderlinePrinterFactory
{
    /**
     * @var NodePrinter
     */
    private $printers;

    public function __construct(NodePrinter $printers)
    {
        $this->printers = $printers;
    }

    public function underline(Node $targetNode): UnderlinePrinter
    {
        return new UnderlinePrinter($this->printers, $targetNode);
    }
}
