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

namespace PhpBench\Extensions\XDebug;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Executor\CompositeExecutor;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extensions\XDebug\Command\Handler\OutputDirHandler;
use PhpBench\Extensions\XDebug\Command\ProfileCommand;
use PhpBench\Extensions\XDebug\Command\TraceCommand;
use PhpBench\Extensions\XDebug\Executor\ProfileExecutor;
use PhpBench\Extensions\XDebug\Executor\TraceExecutor;
use PhpBench\Extensions\XDebug\Renderer\TraceRenderer;

class XDebugExtension implements ExtensionInterface
{
    public function getDefaultConfig()
    {
        return [
            'xdebug.output_dir' => 'xdebug',
        ];
    }

    public function load(Container $container)
    {
        $container->register('xdebug.command.profile', function (Container $container) {
            return new ProfileCommand(
                $container->get('console.command.handler.runner'),
                $container->get('xdebug.command.handler.output_dir')
            );
        }, ['console.command' => []]);

        $container->register('xdebug.command.trace', function (Container $container) {
            return new TraceCommand(
                $container->get('console.command.handler.runner'),
                $container->get('xdebug.renderer.trace'),
                $container->get('xdebug.command.handler.output_dir')
            );
        }, ['console.command' => []]);

        $container->register('xdebug.command.handler.output_dir', function (Container $container) {
            return new OutputDirHandler($container->getParameter('xdebug.output_dir'));
        });

        $container->register('benchmark.executor.xdebug_profile',
            function (Container $container) {
                return new CompositeExecutor(
                    new ProfileExecutor(
                        $container->get(CoreExtension::SERVICE_EXECUTOR_BENCHMARK_MICROTIME)
                    ),
                    $container->get(CoreExtension::SERVICE_EXECUTOR_METHOD_REMOTE)
                );
            },
            ['benchmark_executor' => ['name' => 'xdebug_profile'],
        ]);

        $container->register('xdebug.executor.xdebug_trace',
            function (Container $container) {
                return new CompositeExecutor(
                    new TraceExecutor(
                        $container->get(CoreExtension::SERVICE_EXECUTOR_BENCHMARK_MICROTIME)
                    ),
                    $container->get(CoreExtension::SERVICE_EXECUTOR_METHOD_REMOTE)
                );
            },
            ['benchmark_executor' => ['name' => 'xdebug_trace'],
        ]);

        $container->register('xdebug.renderer.trace', function (Container $container) {
            return new TraceRenderer(
                $container->get('phpbench.formatter')
            );
        });

        $container->mergeParameter('executors', require_once(__DIR__ . '/config/executors.php'));
        $container->mergeParameter('reports', require_once(__DIR__ . '/config/generators.php'));
    }
}
