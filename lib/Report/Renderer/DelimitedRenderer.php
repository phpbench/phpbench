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
use PhpBench\Registry\Config;
use PhpBench\Report\RendererInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Renders the report as a delimited list.
 */
class DelimitedRenderer implements RendererInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Render the table.
     *
     */
    public function render(Document $reportDom, Config $config): void
    {
        /**
         * @phpstan-ignore-next-line
         */
        foreach ($reportDom->firstChild->query('./report') as $reportEl) {
            foreach ($reportEl->query('.//table') as $tableEl) {
                $this->renderTableElement($tableEl, $config);
            }
        }
    }

    protected function renderTableElement(Element $tableEl, $config): void
    {
        $rows = [];

        if (true === $config['header']) {
            $header = [];

            foreach ($tableEl->query('.//row') as $rowEl) {
                foreach ($rowEl->query('.//cell') as $cellEl) {
                    $colName = $cellEl->getAttribute('name');
                    $header[$colName] = $colName;
                }
            }
            $rows[] = $header;
        }

        foreach ($tableEl->query('.//row') as $rowEl) {
            $row = [];

            foreach ($rowEl->query('.//cell') as $cellEl) {
                $colName = $cellEl->getAttribute('name');
                $row[$colName] = $cellEl->nodeValue;
            }

            $rows[] = $row;
        }

        if ($config['file']) {
            $pointer = fopen($config['file'], 'w+');
        } else {
            $pointer = fopen('php://temp', 'w+');
        }

        foreach ($rows as $row) {
            // use fputcsv to handle escaping
            fputcsv($pointer, $row, $config['delimiter']);
        }
        rewind($pointer);
        $this->output->write(stream_get_contents($pointer));
        fclose($pointer);

        if ($config['file']) {
            $this->output->writeln('Dumped delimited file:');
            $this->output->writeln($config['file']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            'delimiter' => "\t",
            'file' => null,
            'header' => true,
        ]);
    }
}
