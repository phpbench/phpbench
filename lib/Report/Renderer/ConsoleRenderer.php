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

use PhpBench\Console\OutputAwareInterface;
use PhpBench\Dom\Document;
use PhpBench\Dom\Element;
use PhpBench\Formatter\Formatter;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\Table\ValueRole;
use PhpBench\Report\RendererInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsoleRenderer implements RendererInterface, OutputAwareInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        $this->configureFormatters($output->getFormatter());
    }

    /**
     * Render the table.
     *
     * @param Document $reportDom
     * @param Config $config
     */
    public function render(Document $reportDom, Config $config)
    {
        /**
         * @phpstan-ignore-next-line
         */
        foreach ($reportDom->firstChild->query('./report') as $reportEl) {
            $title = $reportEl->getAttribute('title');

            if ($title) {
                $this->output->writeln(sprintf('<title>%s</title>', $title));
                $this->output->writeln(sprintf('<title>%s</title>', str_repeat('=', strlen($title))));
                $this->output->write(PHP_EOL);
            }

            foreach ($reportEl->query('./description') as $descriptionEl) {
                $this->output->writeln(sprintf('<description>%s</description>', $descriptionEl->nodeValue));
                $this->output->writeln('');
            }

            foreach ($reportEl->query('.//table') as $tableEl) {
                $this->output->writeln(sprintf('<subtitle>%s</subtitle>', $tableEl->getAttribute('title')));
                $this->renderTableElement($tableEl, $config);
            }
        }
    }

    protected function renderTableElement(Element $tableEl, $config)
    {
        $rows = [];
        $colNames = [];

        foreach ($tableEl->query('.//col') as $colEl) {
            $colNames[] = $colEl->getAttribute('label');
        }

        foreach ($tableEl->query('.//row') as $rowEl) {
            $row = [];
            $formatterParams = [];

            foreach ($rowEl->query('./formatter-param') as $paramEl) {
                $formatterParams[$paramEl->getAttribute('name')] = $paramEl->nodeValue;
            }

            foreach ($rowEl->query('.//cell') as $cellEl) {
                $colName = $cellEl->getAttribute('name');
                $values = [];

                foreach ($cellEl->query('./value') as $valueEl) {
                    $value = $valueEl->nodeValue;
                    $classes = array_filter(explode(' ', $valueEl->getAttribute('class')));

                    if ($classes) {
                        $value = $this->formatter->applyClasses($classes, $value, $formatterParams);
                    }

                    if ($valueEl->getAttribute('role') !== ValueRole::ROLE_PRIMARY) {
                        $value = sprintf('<fg=cyan>%s</>', $value);
                    }

                    $values[] = $value;
                }

                $row[$colName] = implode(' ', $values);
            }

            $rows[] = $row;
        }

        $table = new Table($this->output);

        // style only supported in Symfony > 2.4
        if (method_exists($table, 'setStyle')) {
            $table->setStyle($config['table_style']);
        }

        $table->setHeaders($colNames);
        $table->setRows($rows);
        $this->renderTable($table);
        $this->output->writeln('');
    }

    /**
     * Render the table. For Symfony 2.4 support.
     *
     * @param mixed $table
     */
    private function renderTable($table)
    {
        if (class_exists('Symfony\Component\Console\Helper\Table')) {
            $table->render();

            return;
        }
        $table->render($this->output);
    }

    /**
     * Adds some output formatters.
     *
     * @param OutputFormatterInterface $formatter
     */
    private function configureFormatters(OutputFormatterInterface $formatter)
    {
        $formatter->setStyle(
            'title', new OutputFormatterStyle('white', null, ['bold'])
        );
        $formatter->setStyle(
            'subtitle', new OutputFormatterStyle('white', null, [])
        );
        $formatter->setStyle(
            'description', new OutputFormatterStyle(null, null, [])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options)
    {
        $options->setDefaults([
            'table_style' => 'default',
        ]);
        $options->setAllowedTypes('table_style', ['string']);
    }
}
