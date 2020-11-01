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

use PhpBench\Console\Command\Handler\RunnerHandler;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Executor\Benchmark\RemoteExecutor;
use PhpBench\Executor\CompositeExecutor;
use PhpBench\Executor\Method\RemoteMethodExecutor;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extensions\XDebug\Command\Handler\OutputDirHandler;
use PhpBench\Extensions\XDebug\Command\ProfileCommand;
use PhpBench\Extensions\XDebug\Executor\ProfileExecutor;
use Symfony\Component\OptionsResolver\OptionsResolver;

class XDebugExtension implements ExtensionInterface
{
    const PARAM_OUTPUT_DIR = 'xdebug.command.handler.output_dir';

    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_OUTPUT_DIR => '.phpbench/xdebug-profile',
        ]);
    }

    public function load(Container $container): void
    {
        $container->register(ProfileCommand::class, function (Container $container) {
            return new ProfileCommand(
                $container->get(RunnerHandler::class),
                $container->get(self::PARAM_OUTPUT_DIR)
            );
        }, [
            CoreExtension::TAG_CONSOLE_COMMAND => []
        ]);

        $container->register(self::PARAM_OUTPUT_DIR, function (Container $container) {
            return new OutputDirHandler(
                $container->getParameter(self::PARAM_OUTPUT_DIR)
            );
        });

        $container->register(ProfileExecutor::class, function (Container $container) {
            return new CompositeExecutor(
                new ProfileExecutor(
                    $container->get(RemoteExecutor::class)
                ),
                $container->get(RemoteMethodExecutor::class)
            );
        }, [
            CoreExtension::TAG_EXECUTOR => [
                'name' => 'xdebug_profile'
            ]
        ]);

        $container->mergeParameter('executors', require_once(__DIR__ . '/config/executors.php'));
    }
}
