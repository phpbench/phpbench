<?php

namespace PhpBench\Report\Generator;

use PhpBench\Console\OutputAware;
use PhpBench\Report\ReportManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
    public function configure(OptionsResolver $options)
    {
        $options->setDefaults(array(
            'reports' => array()
        ));
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
