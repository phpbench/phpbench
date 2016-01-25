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

use PhpBench\Console\OutputAwareInterface;
use PhpBench\Dom\Document;
use PhpBench\Registry\Registry;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Manage report configuration and generation.
 *
 * TODO: Create Generator and Renderer factories, reduce the size of this class.
 */
class ReportManager
{
    private $generatorRegistry;
    private $rendererRegistry;

    public function __construct(
        Registry $generatorRegistry,
        Registry $rendererRegistry
    ) {
        $this->generatorRegistry = $generatorRegistry;
        $this->rendererRegistry = $rendererRegistry;
    }

    /**
     * Generate the named reports.
     *
     * @param OutputInterface $output
     * @param Document $suiteDocument
     * @param array $reportNames
     */
    public function generateReports(Document $suiteDocument, array $reportNames)
    {
        $reportDoms = array();
        $reportConfigs = array();
        foreach ($reportNames as $reportName) {
            $reportConfigs[$reportName] = $this->generatorRegistry->getConfig($reportName);
        }

        foreach ($reportConfigs as $reportName => $reportConfig) {
            $generatorName = $reportConfig['generator'];
            $generator = $this->generatorRegistry->getService($generatorName);

            $reportDom = $generator->generate($suiteDocument, $reportConfig);

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
     * @param Document $suiteDocument
     * @param array $reportNames
     * @param array $outputNames
     */
    public function renderReports(OutputInterface $output, Document $suiteDocument, array $reportNames, array $outputNames)
    {
        $reportDoms = $this->generateReports($suiteDocument, $reportNames);

        foreach ($outputNames as $outputName) {
            $outputConfig = $this->rendererRegistry->getConfig($outputName);
            $renderer = $this->rendererRegistry->getService($outputConfig['renderer']);

            // set the output instance if the renderer requires it.
            if ($renderer instanceof OutputAwareInterface) {
                $renderer->setOutput($output);
            }

            foreach ($reportDoms as $reportDom) {
                $renderer->render($reportDom, $outputConfig);
            }
        }
    }
}
