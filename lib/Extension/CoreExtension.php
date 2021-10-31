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

namespace PhpBench\Extension;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Console\Application;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Json\JsonDecoder;
use PhpBench\Logger\ConsoleLogger;
use PhpBench\Util\TimeUnit;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoreExtension implements ExtensionInterface
{
    public const PARAM_EXTENSIONS = 'core.extensions';
    public const PARAM_PROFILES = 'core.profiles';

    public const PARAM_CONFIG_PATH = 'core.config_path';
    public const PARAM_DEBUG = 'core.debug';
    public const PARAM_WORKING_DIR = 'core.working_dir';

    public const PARAM_TIME_UNIT = 'core.time_unit';
    public const PARAM_SCHEMA = '$schema';

    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_SCHEMA => null,
            self::PARAM_DEBUG => false,
            self::PARAM_EXTENSIONS => [],
            self::PARAM_WORKING_DIR => getcwd(),
            self::PARAM_CONFIG_PATH => null,
            self::PARAM_PROFILES => [],
        ]);

        $resolver->setAllowedTypes(self::PARAM_SCHEMA, ['string', 'null']);
        $resolver->setAllowedTypes(self::PARAM_PROFILES, ['array']);
        $resolver->setAllowedTypes(self::PARAM_DEBUG, ['bool']);
        $resolver->setAllowedTypes(self::PARAM_CONFIG_PATH, ['string', 'null']);
        $resolver->setAllowedTypes(self::PARAM_EXTENSIONS, ['string[]']);
        $resolver->setAllowedTypes(self::PARAM_WORKING_DIR, ['string']);
        SymfonyOptionsResolverCompat::setInfos($resolver, [
            self::PARAM_PROFILES => <<<'EOT'
Alternative configurations::

    {
        "core.profiles": {
            "php8": {
                "runner.php_bin": "/bin/php8"
            }
        }
    }

The named configuration will be merged with the default configuration, and can be used via:

.. code-block:: bash

    $ phpbench run --profile=php8
EOT
            ,
            self::PARAM_DEBUG => 'If enabled output debug messages (e.g. the commands being executed when running benchamrks). Same as ``-vvv``',
            self::PARAM_EXTENSIONS => 'List of additional extensions to enable',
            self::PARAM_CONFIG_PATH => 'Alternative path to a PHPBench configuration file (default is ``phpbench.json``',
            self::PARAM_WORKING_DIR => 'Working directory to use',
            self::PARAM_SCHEMA => 'JSON schema location, e.g. ``./vendor/phpbench/phpbench/phpbench.schema.json``',
        ]);
    }

    public function load(Container $container): void
    {
        $container->register(Application::class, function (Container $container) {
            $application = new Application();

            foreach (array_keys($container->getServiceIdsForTag(ConsoleExtension::TAG_CONSOLE_COMMAND)) as $serviceId) {
                $command = $container->get($serviceId);
                $application->add($command);
            }

            return $application;
        });

        $container->register(LoggerInterface::class, function (Container $container) {
            return new ConsoleLogger(
                $container->get(ConsoleExtension::SERVICE_OUTPUT_ERR),
                $container->getParameter(self::PARAM_DEBUG)
            );
        });

        $container->register(TimeUnit::class, function (Container $container) {
            return new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MICROSECONDS);
        });

        $this->registerJson($container);
        $this->registerCommands($container);
    }

    private function registerJson(Container $container): void
    {
        $container->register(JsonDecoder::class, function (Container $container) {
            return new JsonDecoder();
        });
    }

    private function registerCommands(Container $container): void
    {
        $container->register(TimeUnitHandler::class, function (Container $container) {
            return new TimeUnitHandler(
                $container->get(TimeUnit::class)
            );
        });
    }
}
