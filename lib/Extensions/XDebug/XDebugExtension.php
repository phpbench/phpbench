<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\XDebug;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use PhpBench\Extensions\XDebug\Executor\XDebugTraceExecutor;

class XDebugExtension implements ExtensionInterface
{
    public function configure(Container $container)
    {
        $container->register('benchmark.executor.xdebug_trace', function (Container $container) {
            return new XDebugTraceExecutor(
                $container->get('benchmark.remote.launcher')
            );
        },array('executor' => array('name' => 'xdebug-trace')));
    }

    public function build(Container $container)
    {
    }
}
