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
use PhpBench\Registry\Config;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Report\Model\Reports;

/**
 * Manage report configuration and generation.
 */
class ReportManager
{
    /**
     * @var ConfigurableRegistry<GeneratorInterface>
     */
    private $generatorRegistry;

    /**
     * @var ConfigurableRegistry<RendererInterface>
     */
    private $rendererRegistry;

    /**
     * @param ConfigurableRegistry<GeneratorInterface> $generatorRegistry
     * @param ConfigurableRegistry<RendererInterface> $rendererRegistry
     */
    public function __construct(
        ConfigurableRegistry $generatorRegistry,
        ConfigurableRegistry $rendererRegistry
    ) {
        $this->generatorRegistry = $generatorRegistry;
        $this->rendererRegistry = $rendererRegistry;
    }

    /**
     * @param string[] $reportNames
     */
    public function validateReportNames(array $reportNames): void
    {
        foreach ($reportNames as $reportName) {
            $this->generatorRegistry->getConfig($reportName);
        }
    }

    /**
     * @param string[] $reportNames
     * @param string[] $outputNames
     */
    public function renderReports(SuiteCollection $collection, array $reportNames, array $outputNames): void
    {
        $reports = $this->generateReports($collection, $reportNames);

        foreach ($outputNames as $outputName) {
            $outputConfig = $this->rendererRegistry->getConfig($outputName);
            $renderer = $this->rendererRegistry->getService($outputConfig['renderer']);
            assert($renderer instanceof RendererInterface);

            $renderer->render($reports, $outputConfig);
        }
    }

    /**
     * @param string[] $reportNames
     */
    public function generateReports(SuiteCollection $collection, array $reportNames): Reports
    {
        $reportConfigs = (array)array_combine($reportNames, array_map(function (string $reportName): Config {
            return $this->generatorRegistry->getConfig($reportName);
        }, $reportNames));

        $reports = Reports::empty();

        foreach ($reportConfigs as $reportName => $reportConfig) {
            assert($reportConfig instanceof Config);

            $generatorName = $reportConfig['generator'];
            $generator = $this->generatorRegistry->getService($generatorName);

            $reports = $reports->merge($generator->generate($collection, $reportConfig));
        }

        return $reports;
    }
}
