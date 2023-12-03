<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Exception\PrinterError;

final class NodePrinters implements NodePrinter
{
    /**
     * @param NodePrinter[] $printers
     */
    public function __construct(private readonly array $printers)
    {
    }

    /**
     */
    public function print(Printer $printer, Node $node): string
    {
        foreach ($this->printers as $nodePrinter) {
            $output = $nodePrinter->print($printer, $node);

            if (null === $output) {
                continue;
            }

            return $output;
        }

        throw new PrinterError(sprintf(
            'Could not find printer for node of class "%s"',
            $node::class
        ));
    }
}
