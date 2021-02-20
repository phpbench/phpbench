<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Exception\PrinterError;

final class NormalizingPrinter implements Printer
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

    public function print(Node $node, array $params): string
    {
        foreach ($this->printers as $printer) {
            $output = $printer->print($this, $node, $params);

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
