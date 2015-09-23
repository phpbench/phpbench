<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report;

use PhpBench\Benchmark\SuiteDocument;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface GeneratorInterface
{
    /**
     * Return a JSON schema which should be used to validate the configuration.
     * Return an empty array() if you want to allow anything.
     *
     * @param OptionsResolver $options
     */
    public function getSchema();

    /**
     * Generate the report.
     *
     * @param SuiteDocument $collection
     * @param array $config
     */
    public function generate(SuiteDocument $collection, array $config);

    /**
     * Return an array of report configurations keyed by the report name
     * that should be available by default.
     *
     * @return array
     */
    public function getDefaultReports();

    /***
     * Return the default configuration. This configuration will be prepended
     * to all subsequent reports and should be used to provide default values.
     *
     * @return array
     */

    public function getDefaultConfig();
}
