<?php

namespace PhpBench\Template\Expression\Printer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Printer;

final class TemplatePrinter implements Printer
{
    /**
     * @var Printer
     */
    private $printer;

    public function __construct(Printer $printer)
    {
        $this->printer = $printer;
    }

    public function print(Node $node): string
    {
        return $this->printer->print(
            new SkipTemplateNode($node)
        );
    }
}
