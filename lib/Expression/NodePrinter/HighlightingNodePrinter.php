<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\ColorMap;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;

class HighlightingNodePrinter implements NodePrinter
{
    /**
     * @var NodePrinter
     */
    private $nodePrinter;

    /**
     * @var ColorMap<Node>
     */
    private $colorMap;

    /**
     * @param ColorMap<Node> $colorMap
     */
    public function __construct(NodePrinter $nodePrinter, ColorMap $colorMap)
    {
        $this->nodePrinter = $nodePrinter;
        $this->colorMap = $colorMap;
    }

    /**
     * {@inheritDoc}
     */
    public function print(Printer $printer, Node $node): ?string
    {
        $printed = $this->nodePrinter->print($printer, $node);

        $map = $this->colorMap->colors();

        foreach ($map as $nodeFqn => $color) {
            if (!$node instanceof $nodeFqn) {
                continue;
            }

            if (is_callable($color)) {
                $color = $color($node);
            }

            return sprintf(
                '<%s>%s</>',
                $color,
                $printed
            );
        }

        return $printed;
    }
}
