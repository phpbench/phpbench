<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Generator;

use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Console\OutputAwareInterface;
use PhpBench\Dom\Document;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\ReportManager;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Report generator which is a composite of other named reports.
 */
class CompositeGenerator implements GeneratorInterface, OutputAwareInterface
{
    /**
     * @var ReportManager
     */
    private $reportManager;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param ReportManager $reportManager
     */
    public function __construct(ReportManager $reportManager)
    {
        $this->reportManager = $reportManager;
    }

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
    public function getSchema()
    {
        return array(
            'type' => 'object',
            'properties' => array(
                'reports' => array(
                    'title' => 'List of reports to use',
                    'type' => 'array',
                ),
            ),
            'required' => array('reports'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SuiteDocument $result, Config $config)
    {
        $reportDoms = $this->reportManager->generateReports($result, $config['reports']);
        $compositeDom = new Document();
        $compositeEl = $compositeDom->createRoot('reports');
        $compositeEl->setAttribute('name', $config->getName());

        foreach ($reportDoms as $reportsDom) {
            foreach ($reportsDom->xpath()->query('./report') as $reportDom) {
                $reportEl = $compositeDom->importNode($reportDom, true);
                $compositeEl->appendChild($reportEl);
            }
        }

        return $compositeDom;
    }
}
