<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinters;

final class UnderlinePrinterFactory
{
    /**
     * @var NodePrinters
     */
    private $printers;

    public function __construct(NodePrinters $printers)
    {
        $this->printers = $printers;
    }

    public function underline(Node $targetNode): UnderlinePrinter
    {
        return new UnderlinePrinter($this->printers, $targetNode);
    }
}
