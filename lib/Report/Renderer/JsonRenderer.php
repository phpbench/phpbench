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

use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Evaluator;
use PhpBench\Registry\Config;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Model\Table;
use PhpBench\Report\RendererInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Renders tables from reports as JSON.
 */
class JsonRenderer implements RendererInterface
{
    public function __construct(private readonly OutputInterface $output, private readonly Evaluator $evaluator)
    {
    }

    /**
     * Render the table.
     *
     */
    public function render(Reports $reports, Config $config): void
    {
        foreach ($reports->tables() as $table) {
            $this->renderTable($table, $config);
        }
    }

    /**
     * @param Config $config
     */
    protected function renderTable(Table $table, $config): void
    {
        $rows = [];

        foreach ($table as $tableRow) {
            $row = [];

            foreach ($tableRow as $name => $node) {
                $evaluation = $this->evaluator->evaluate($node, []);

                if (!$evaluation instanceof PhpValue) {
                    continue;
                }
                $row[$name] = $evaluation->value();
            }

            $rows[] = $row;
        }

        $this->output->writeln(json_encode($rows));
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options): void
    {
    }
}
