<?php

namespace PhpBench\Report\Console\Renderer;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Printer;
use PhpBench\Report\Console\ObjectRenderer;
use PhpBench\Report\Console\ObjectRendererInterface;
use PhpBench\Report\Model\Table;
use PhpBench\Report\Model\TableRow;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Output\OutputInterface;

class TableRenderer implements ObjectRendererInterface
{
    /**
     * @var Printer
     */
    private $printer;

    public function __construct(Printer $printer)
    {
        $this->printer = $printer;
    }

    public function render(OutputInterface $output, ObjectRenderer $renderer, object $object): bool
    {
        if (!$object instanceof Table) {
            return false;
        }

        $rows = [];

        if ($object->title()) {
            $output->writeln(sprintf('%s', $object->title()));
        }

        $consoleTable = new SymfonyTable($output);
        $consoleTable->setHeaders($object->columnNames());
        $consoleTable->setRows($this->buildRows($object));
        $consoleTable->render();
        $output->writeln('');

        return true;
    }

    /**
     * @return array<array<string,mixed>>
     */
    private function buildRows(Table $table): array
    {
        return array_map(function (TableRow $row) {
            return array_map(function (Node $node) {
                return $this->printer->print($node);
            }, $row->cells());
        }, $table->rows());
    }
}
