<?php

namespace PhpBench\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainPrinter;
use PhpBench\Expression\NodePrinter;

class ConsoleStylePrinter implements NodePrinter
{
    /**
     * @var NodePrinter
     */
    private $innerPrinter;

    /**
     * @var string
     */
    private $style;

    public function __construct(NodePrinter $innerPrinter, string $style)
    {
        $this->innerPrinter = $innerPrinter;
        $this->style = $style;
    }

    public function print(MainPrinter $printer, Node $node, array $params): ?string
    {
        $out = $this->innerPrinter->print($printer, $node, $params);
        if (null === $out) {
            return null;
        }

        return sprintf('<%s>%s</>', $this->style, $out);
    }
}
