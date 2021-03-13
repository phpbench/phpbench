<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Exception\PrinterError;

final class NodePrinters implements NodePrinter
{
    /**
     * @var NodePrinter[]
     */
    private $printers;

    /**
     * @param NodePrinter[] $printers
     */
    public function __construct(array $printers)
    {
        $this->printers = $printers;
    }

    /**
     * @param parameters $params
     */
    public function print(Printer $printer, Node $node, array $params): string
    {
        foreach ($this->printers as $nodePrinter) {
            $output = $nodePrinter->print($printer, $node, $params);

            if (null === $output) {
                continue;
            }

            return $output;
        }

        throw new PrinterError(sprintf(
            'Could not find printer for node of class "%s"',
            get_class($node)
        ));
    }
}
