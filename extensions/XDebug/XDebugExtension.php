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

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Extensions\XDebug\Command\ProfileCommand;
use PhpBench\Extensions\XDebug\Executor\XDebugExecutor;

class XDebugExtension implements ExtensionInterface
{
    public function configure(Container $container)
    {
        $container->register('xdebug.command.profile', function (Container $container) {
            return new ProfileCommand(
                $container->get('console.command.handler.runner')
            );
        }, array('console.command' => array()));

        $container->registerFactory('benchmark.executor.xdebug', 
            function (Container $container, $options) {
                return new XDebugExecutor(
                    $container->get('benchmark.remote.launcher'),
                    $options['output_dir'],
                    $options['callback']
                );
            },
            array('executor' => array('name' => 'xdebug'),
            array(
                'output_dir' => 'profile',
                'callback' => function() {},
            ),
            __DIR__ . '/schema/xdebug_executor.json'
        ));
    }

    public function build(Container $container)
    {
    }
}
