<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report;

use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Dom\Document;
use PhpBench\Config\ConfigurableInterface;

interface GeneratorInterface extends ConfigurableInterface
{
    /**
     * Generate the report document from the suite result document.
     *
     * @param SuiteDocument $suiteDocument
     * @param array $config
     *
     * @return Document
     */
    public function generate(SuiteDocument $suiteResult, array $config);

    /**
     * Return an array of report configurations keyed by the report name
     * that should be available by default.
     *
     * @return array
     */
    public function getDefaultReports();
}
