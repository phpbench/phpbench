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

    public function validateReportNames($reportNames): void
    {
        foreach ($reportNames as $reportName) {
            $this->generatorRegistry->getConfig($reportName);
        }
    }

    /**
     * Generate the named reports.
     *
     */
    public function generateReports(SuiteCollection $collection, array $reportNames): array
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
            $reportDom->schemaValidate(__DIR__ . '/schema/report.xsd');

            $reportDoms[] = $reportDom;
        }

        return $reportDoms;
    }

    /**
     * Render reports (as opposed to just generating the report XML documents via. generateReports).
     *
     */
    public function renderReports(
        OutputInterface $output,
        SuiteCollection $collection,
        array $reportNames,
        array $outputNames
    ): void {
        $reportDoms = $this->generateReports($collection, $reportNames);

        foreach ($outputNames as $outputName) {
            $outputConfig = $this->rendererRegistry->getConfig($outputName);
            $renderer = $this->rendererRegistry->getService($outputConfig['renderer']);
            assert($renderer instanceof RendererInterface);

            foreach ($reportDoms as $reportDom) {
                $renderer->render($reportDom->duplicate(), $outputConfig);
            }
        }
    }
}
