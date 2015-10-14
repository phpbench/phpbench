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
use PhpBench\Extensions\XDebug\Command\ProfileCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\Extensions\XDebug\Command\TraceCommand;

class XDebugExtension implements ExtensionInterface
{
    public function configure(Container $container)
    {
        $this->registerCommands($container);
    }

    public function build(Container $container)
    {
    }

    private function registerCommands(Container $container)
    {
        $container->register('xdebug.command.profile', function (Container $container) {
            return new ProfileCommand(
                $container->get('benchmark.remote.launcher'),
                $container->get('benchmark.collection_builder')
            );
        }, array('console.command' => array()));

        $container->register('xdebug.command.trace', function (Container $container) {
            return new TraceCommand(
                $container->get('benchmark.remote.launcher'),
                $container->get('benchmark.collection_builder'),
                $container->get('tabular')
            );
        }, array('console.command' => array()));
    }
}
