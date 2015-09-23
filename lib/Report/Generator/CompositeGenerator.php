<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Generator;

use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Console\OutputAwareInterface;
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
    public function generate(SuiteDocument $result, array $config)
    {
        $this->reportManager->generateReports($this->output, $result, $config['reports']);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultReports()
    {
        return array();
    }
}
