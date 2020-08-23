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

namespace PhpBench\Report\Generator;

use PhpBench\Console\OutputAwareInterface;
use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Report generator for environmental information.
 *
 * NOTE: The Table report generator could probably be improved to be able to incorporate
 *       this report somehow.
 */
class EnvGenerator implements GeneratorInterface, OutputAwareInterface
{
    private $output;

    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options)
    {
        $options->setDefaults([
            'title' => null,
            'description' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SuiteCollection $suiteCollection, Config $config)
    {
        $document = new Document();
        $reportsEl = $document->createRoot('reports');
        $reportsEl->setAttribute('name', 'table');
        $reportEl = $reportsEl->appendElement('report');

        if (isset($config['title'])) {
            $reportEl->setAttribute('title', $config['title']);
        }

        if (isset($config['description'])) {
            $reportEl->appendElement('description', $config['description']);
        }

        foreach ($suiteCollection as $suite) {
            $tableEl = $reportEl->appendElement('table');
            $colsEl = $tableEl->appendElement('cols');

            foreach (['provider', 'key', 'value'] as $colName) {
                $col = $colsEl->appendElement('col');
                $col->setAttribute('name', $colName);
                $col->setAttribute('label', $colName);
            }

            $tableEl->setAttribute('title', sprintf(
                'Suite #%s %s', $suite->getUuid(), $suite->getDate()->format('Y-m-d H:i:s')
            ));

            $groupEl = $tableEl->appendElement('group');
            $groupEl->setAttribute('name', 'body');

            foreach ($suite->getEnvInformations() as $envInformation) {
                foreach ($envInformation as $key => $value) {
                    $rowEl = $groupEl->appendElement('row');

                    $cellEl = $rowEl->appendElement('cell', $envInformation->getName());
                    $valueEl = $cellEl->appendElement('value', $envInformation->getName());
                    $cellEl->setAttribute('name', 'provider');
                    $cellEl = $rowEl->appendElement('cell');
                    $valueEl = $cellEl->appendElement('value', $key);
                    $cellEl->setAttribute('name', 'key');
                    $cellEl = $rowEl->appendElement('cell');
                    $valueEl = $cellEl->appendElement('value', is_bool($value) ? $value ? 'yes' : 'no' : $value);
                    $cellEl->setAttribute('name', 'value');
                }
            }
        }

        return $document;
    }
}
