<?php

namespace PhpBench\Extensions\RProject;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use PhpBench\Extensions\RProject\Report\Renderer\RProjectRenderer;
use PhpBench\Extensions\RProject\Report\Generator\RScriptGenerator;

class RProjectExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function configure(Container $container)
    {
        $container->register('rpoject.report.generator', function (Container $container) {
            return new RScriptGenerator();
        }, array('report_generator' => array('name' => 'rscript')));
    }

    /**
     * {@inheritDoc}
     */
    public function build(Container $container)
    {
    }
}
