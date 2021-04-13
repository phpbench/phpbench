<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

final class UnderlinePrinter implements Printer
{
    /**
     * @var NodePrinter
     */
    private $printers;

    /**
     * @var Node
     */
    private $targetNode;

    public function __construct(NodePrinter $printers, Node $targetNode)
    {
        $this->printers = $printers;
        $this->targetNode = $targetNode;
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
