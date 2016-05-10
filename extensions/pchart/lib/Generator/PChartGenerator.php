<?php

namespace PhpBench\Extensions\PChart\Generator;

use PhpBench\Report\Generator\AbstractTableGenerator;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use CpChart\Factory\Factory;
use PhpBench\Dom\Document;
use Symfony\Component\Filesystem\Filesystem;

class PChartGenerator extends AbstractTableGenerator
{
    private $factory;
    private $filesystem;

    public function __construct(Factory $factory = null, Filesystem $filesystem = null)
    {
        $this->factory = $factory ?: new Factory();
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return array_merge(
            parent::getDefaultConfig(),
            [
                'plots' => [],
                'height' => 350,
                'width' => 1000,
                'padding' => 75,
                'type' => 'line',
                'x_label' => 'iter',
                'legend' => true,
                'legend_x' => 25,
                'legend_y' => 25,
                'output_dir' => getcwd() . '/charts',
                'weight' => 1,
                'col' => 'time',
            ]
        );
    }

    public function getSchema()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SuiteCollection $suiteCollection, Config $config)
    {
        $table = $this->buildTable($suiteCollection, $config);
        $table = $this->processSort($table, $config);
        $tables = $this->processBreak($table, $config);
        $tables = $this->processCompare($tables, $config);

        $ySerieses = [];
        $xLabels = [];

        $pData = $this->factory->newData();

        foreach ($tables as $break => $table) {
            $ySeries = [];
            $xLabels = [];
            foreach ($table as $row) {
                $ySeries[] = $row[$config['col']];
                if ($config['x_label']) {
                    $xLabels[] = $row[$config['x_label']];
                }
            }
            $ySerieses[$break] = $ySeries;
        }

        foreach ($ySerieses as $break => $ySeries) {
            $pData->addPoints($ySeries, $break);
            $pData->setSerieWeight($break, $config['weight']);
        }
        $pData->addPoints($xLabels, '__labels');
        $pData->setAbscissa('__labels');

        $pGraph = $this->factory->newImage($config['width'], $config['height'], $pData);
        $pGraph->setGraphArea(
            $config['padding'], $config['padding'], 
            $config['width'] - $config['padding'], $config['height'] - $config['padding']
        );

        if ((bool) $config['legend']) {
            $pGraph->drawLegend(
                $config['padding'] + $config['legend_x'], 
                $config['padding'] + $config['legend_y']
            );
        }

        $pGraph->drawScale();

        $types = [
            'line' => 'drawLineChart',
            'plot' => 'drawPlotChart',
            'bar' => 'drawBarChart',
            'spline' => 'drawSplineChart',
            'filled_spline' => 'drawFilledSplineChart',
            'stacked_area' => 'drawStackedAreaChart',
        ];

        if (!isset($types[$config['type']])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid graph type "%s", valid types: "%s"',
                $config['type'],
                implode('", "', array_keys($types))
            ));
        }

        // render the graph
        $pGraph->{$types[$config['type']]}();

        $fpath = sprintf(
            '%s/%s.png',
            $config['output_dir'],
            uniqid()
        );
        if (!$this->filesystem->exists($config['output_dir'])) {
            $this->filesystem->mkdir($config['output_dir']);
        }

        $pGraph->Render($fpath);

        // generate the document
        $document = new Document();
        $reportsEl = $document->createRoot('reports');
        $reportsEl->setAttribute('name', 'table');
        $reportEl = $reportsEl->appendElement('report');
        $imageEl = $reportEl->appendElement('image');
        $imageEl->setAttribute('path', $fpath);

        return $document;
    }
}
