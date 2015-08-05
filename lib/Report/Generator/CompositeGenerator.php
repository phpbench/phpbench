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

use PhpBench\Console\OutputAware;
use PhpBench\Report\ReportManager;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\ReportGenerator;
use PhpBench\Result\SuiteResult;

/**
 * Report generator which is a composite of other named reports.
 */
class CompositeGenerator implements ReportGenerator, OutputAware
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
     * {@inheritDoc}
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteResult $result, array $config)
    {
        $this->reportManager->generateReports($this->output, $result, $config['reports']);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultReports()
    {
        return array();
    }
}
