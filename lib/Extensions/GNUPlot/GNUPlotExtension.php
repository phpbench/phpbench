<?php

namespace PhpBench\Extensions\GNUPlot;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use PhpBench\Extensions\GNUPlot\Report\Renderer\GNUPlotRenderer;
use PhpBench\Extensions\GNUPlot\Report\Generator\GNUPlotGenerator;

class GNUPlotExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function configure(Container $container)
    {
        $container->register('rpoject.report.generator', function (Container $container) {
            return new GNUPlotGenerator();
        }, array('report_generator' => array('name' => 'gnuplot')));
    }

    /**
     * {@inheritDoc}
     */
    public function build(Container $container)
    {
    }
}
