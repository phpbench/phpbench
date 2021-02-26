<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinters;
use PhpBench\Expression\Printer;

final class UnderlinePrinter implements Printer
{
    /**
     * @var NodePrinters
     */
    private $printers;

    /**
     * @var Node
     */
    private $targetNode;

    public function __construct(NodePrinters $printers, Node $targetNode)
    {
        $this->printers = $printers;
        $this->targetNode = $targetNode;
    }

    public function print(Node $node, array $params): string
    {
        $printed = $this->printers->print($this, $node, $params);

        if ($node !== $this->targetNode) {
            return preg_replace('{[^-]}', ' ', $printed);
        }

        return str_repeat('-', mb_strlen($printed));
    }
}
