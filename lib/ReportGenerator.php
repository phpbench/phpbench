<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Result\SuiteResult;
use Symfony\Component\Console\Output\OutputInterface;

interface ReportGenerator
{
    /**
     * Configure the options for the report
     *
     * @param OptionsResolver $options
     */
    public function configure(OptionsResolver $options);

    /**
     * Generate the report
     *
     * @param SuiteResult $collection
     * @param array $config
     */
    public function generate(SuiteResult $collection, array $config);

    /**
     * Return an array of report configurations keyed by the report name
     * that should be available by default
     *
     * @return array
     */
    public function getDefaultReports();
}
