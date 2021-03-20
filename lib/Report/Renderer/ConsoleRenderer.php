<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Report\Renderer;

use PhpBench\Dom\Document;
use PhpBench\Dom\Element;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Printer;
use PhpBench\Formatter\Formatter;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\Table\ValueRole;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Table as PhpBenchTable;
use PhpBench\Report\Model\TableRow;
use PhpBench\Report\RendererInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsoleRenderer implements RendererInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Printer
     */
    private $printer;

    public function __construct(OutputInterface $output, Printer $printer)
    {
        $this->output = $output;
        $this->printer = $printer;
    }

    /**
     * Render the table.
     *
     */
    public function render(Report $report, Config $config): void
    {
        if ($title = $report->title()) {
            $this->output->writeln(sprintf('<title>%s</title>', $title));
            $this->output->writeln(sprintf('<title>%s</title>', str_repeat('=', strlen($title))));
            $this->output->write(PHP_EOL);
        }

        if ($description = $report->description()) {
            $this->output->writeln(sprintf('<title>%s</title>', $title));
            $this->output->writeln(sprintf('<description>%s</description>', $description));
            $this->output->writeln('');
        }

        foreach ($report->tables() as $table) {
            $this->output->writeln(sprintf('%s', $table->title()));
            $this->renderTableElement($table, $config);
        }
    }

    protected function renderTableElement(PhpBenchTable $table, $config): void
    {
        $rows = [];

        $consoleTable = new Table($this->output);
        $consoleTable->setStyle($config['table_style']);
        $consoleTable->setHeaders($table->columnNames());
        $consoleTable->setRows($this->buildRows($table));
        $consoleTable->render();
        $this->output->writeln('');
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            'table_style' => 'default',
        ]);
        $options->setAllowedTypes('table_style', ['string']);
    }

    private function buildRows(PhpBenchTable $table): array
    {
        return array_map(function (TableRow $row) {
            return array_map(function (Node $node) {
                return $this->printer->print($node, []);
            }, $row->cells());

        }, $table->rows());
    }
}
