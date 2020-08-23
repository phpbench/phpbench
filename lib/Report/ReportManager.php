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

namespace PhpBench\Report;

use PhpBench\Console\OutputAwareInterface;
use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\ConfigurableRegistry;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manage report configuration and generation.
 */
class ReportManager
{
    private $generatorRegistry;
    private $rendererRegistry;

    public function __construct(
        ConfigurableRegistry $generatorRegistry,
        ConfigurableRegistry $rendererRegistry
    ) {
        $this->generatorRegistry = $generatorRegistry;
        $this->rendererRegistry = $rendererRegistry;
    }

    public function validateReportNames($reportNames)
    {
        foreach ($reportNames as $reportName) {
            $this->generatorRegistry->getConfig($reportName);
        }
    }

    /**
     * Generate the named reports.
     *
     * @param SuiteCollection $collection
     * @param array $reportNames
     *
     * @return array
     */
    public function generateReports(SuiteCollection $collection, array $reportNames)
    {
        $reportDoms = [];
        $reportConfigs = [];

        foreach ($reportNames as $reportName) {
            $reportConfigs[$reportName] = $this->generatorRegistry->getConfig($reportName);
        }

        foreach ($reportConfigs as $reportName => $reportConfig) {
            $generatorName = $reportConfig['generator'];
            $generator = $this->generatorRegistry->getService($generatorName);

            $reportDom = $generator->generate($collection, $reportConfig);

            if (!$reportDom instanceof Document) {
                throw new \RuntimeException(sprintf(
                    'Report generator "%s" should have return a PhpBench\Dom\Document class, got: "%s"',
                    $generatorName,
                    is_object($reportDom) ? get_class($reportDom) : gettype($reportDom)
                ));
            }

            $reportDom->schemaValidate(__DIR__ . '/schema/report.xsd');

            $reportDoms[] = $reportDom;
        }

        return $reportDoms;
    }

    /**
     * Render reports (as opposed to just generating the report XML documents via. generateReports).
     *
     * @param OutputInterface $output
     * @param SuiteCollection $collection
     * @param array $reportNames
     * @param array $outputNames
     */
    public function renderReports(
        OutputInterface $output,
        SuiteCollection $collection,
        array $reportNames,
        array $outputNames
    ) {
        $reportDoms = $this->generateReports($collection, $reportNames);

        foreach ($outputNames as $outputName) {
            $outputConfig = $this->rendererRegistry->getConfig($outputName);
            $renderer = $this->rendererRegistry->getService($outputConfig['renderer']);
            assert($renderer instanceof RendererInterface);

            // set the output instance if the renderer requires it.
            if ($renderer instanceof OutputAwareInterface) {
                $renderer->setOutput($output);
            }

            foreach ($reportDoms as $reportDom) {
                $renderer->render($reportDom->duplicate(), $outputConfig);
            }
        }
    }
}
