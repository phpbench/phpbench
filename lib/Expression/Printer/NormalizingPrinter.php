<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

final class NormalizingPrinter implements Printer
{
    /**
     * @var NodePrinter
     */
    private $printers;

    public function __construct(NodePrinter $printers)
    {
        $this->printers = $printers;
    }

    public function print(Node $node): string
    {
        return $this->printers->print($this, $node);
    }
}
