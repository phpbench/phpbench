<?php

namespace PhpBench\Template\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\NodePrinters;
use PhpBench\Expression\Printer;
use PhpBench\Template\Exception\CouldNotFindTemplateForObject;
use PhpBench\Template\ObjectRenderer;

class TemplatePrinter implements NodePrinter
{
    /**
     * @var array<string, bool>
     */
    private $seen = [];

    /**
     * @var array<string, string>
     */
    private $cached = [];

    /**
     * @var NodePrinters
     */
    private $nodePrinters;

    /**
     * @var ObjectRenderer
     */
    private $renderer;

    public function __construct(ObjectRenderer $renderer, NodePrinters $nodePrinters)
    {
        $this->nodePrinters = $nodePrinters;
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function print(Printer $printer, Node $node): string
    {
        $hash = serialize($node);

        if (isset($this->cached[$hash])) {
            return $this->cached[$hash];
        }

        if (isset($this->seen[$hash])) {
            return $this->nodePrinters->print($printer, $node);
        }

        $this->seen[$hash] = true;

        try {
            $this->cached[$hash] = $this->renderer->render($node);

            return $this->cached[$hash];
        } catch (CouldNotFindTemplateForObject $couldNotFind) {
            return $this->nodePrinters->print($printer, $node);
        }
    }
}
