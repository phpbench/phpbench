<?php

namespace PhpBench\Extensions\Html\Expression\NodePrinter;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\LabelNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PercentDifferenceNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Theme\Util\Color;
use PhpBench\Expression\Theme\Util\Gradient;

class HtmlHighlightingNodePrinter implements NodePrinter
{
    /**
     * @var NodePrinter
     */
    private $innerPrinter;

    public function __construct(NodePrinter $innerPrinter)
    {
        $this->innerPrinter = $innerPrinter;
    }

    /**
     * {@inheritDoc}
     */
    public function print(Printer $printer, Node $node): ?string
    {
        return $this->decorate($node, $this->innerPrinter->print($printer, $node));
    }

    private function decorate(Node $node, string $string): string
    {
        if ($node instanceof UnitNode) {
            return $this->span('phpbench unit', $string);
        }

        if ($node instanceof LabelNode) {
            return $this->span('phpbench label', $string);
        }

        if ($node instanceof StringNode) {
            return $this->span('phpbench string', $string);
        }

        if ($node instanceof BooleanNode) {
            return $this->span(sprintf('phpbench bool-%s', $node->value() ? 'true' : 'false'), $string);
        }

        if ($node instanceof PercentDifferenceNode) {
            $gradient = Gradient::start(
                Color::fromHex('#00aa00')
            )->to(
                Color::fromHex('#000000'),
                100
            )->to(
                Color::fromHex('#ff0000'),
                100
            );

            return sprintf(
                '<span style="color: #%s">%s</span>',
                $gradient->colorAtPercentile($node->percentage() + 50)->toHex(),
                $string
            );
        }

        return $string;
    }

    private function span(string $class, string $string): string
    {
        return sprintf('<span class="%s">%s</span>', $class, $string);
    }
}
