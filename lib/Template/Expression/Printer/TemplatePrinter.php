<?php

namespace PhpBench\Template\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Printer;

final class TemplatePrinter implements Printer
{
    public function __construct(private readonly Printer $printer)
    {
    }

    public function print(Node $node): string
    {
        return $this->printer->print(
            new SkipTemplateNode($node)
        );
    }
}
