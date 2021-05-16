<?php

namespace PhpBench\Extensions\Html\Template;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;
use PhpBench\Extensions\Html\ObjectRenderer;
use PhpBench\Extensions\Html\ObjectRenderers;

class NodeRenderer implements ObjectRenderer
{
    /**
     * @var Printer
     */
    private $printer;

    public function __construct(Printer $printer)
    {
        $this->printer = $printer;
    }

    public function render(ObjectRenderers $renderer, object $object): ?string
    {
        if (!$object instanceof Node) {
            return null;
        }

        return $this->printer->print($object);
    }
}
