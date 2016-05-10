<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\PChart;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Extensions\XDebug\Command\ProfileCommand;
use PhpBench\Extensions\XDebug\Executor\XDebugExecutor;
use PhpBench\Extensions\PChart\Generator\PChartGenerator;

class PChartExtension implements ExtensionInterface
{
    public function getDefaultConfig()
    {
        return [];
    }

    public function load(Container $container)
    {
        $container->register('pchart.generator.pchart', function () {
            return new PChartGenerator();
        }, [ 'report_generator' => [ 'name' => 'pchart' ] ]);
    }

    public function build(Container $container)
    {
        $container->setParameter('reports', array_merge(
            require(__DIR__ . '/config/generators.php'),
            $container->getParameter('reports')
        ));
    }
}
