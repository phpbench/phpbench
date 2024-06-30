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
use PhpBench\Config\ConfigManipulator;
use PhpBench\Console\Application;
use PhpBench\Console\Command\ConfigExtendCommand;
use PhpBench\Console\Command\ConfigInitCommand;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Json\JsonDecoder;
use PhpBench\Logger\ConsoleLogger;
use PhpBench\Registry\Registries;
use PhpBench\Util\TimeUnit;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoreExtension implements ExtensionInterface
{
    final public const PARAM_EXTENSIONS = 'core.extensions';
    final public const PARAM_PROFILES = 'core.profiles';

    final public const PARAM_CONFIG_PATH = 'core.config_path';
    final public const PARAM_DEBUG = 'core.debug';
    final public const PARAM_WORKING_DIR = 'core.working_dir';

    final public const PARAM_TIME_UNIT = 'core.time_unit';
    final public const PARAM_SCHEMA = '$schema';
    public const TAG_REGISTRY = 'core.registry';

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
        $this->registerConfig($container);
        $this->registerCommands($container);
        $this->registerRegistries($container);
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
        $container->register(ConfigInitCommand::class, function (Container $container) {
            return new ConfigInitCommand($container->get(ConfigManipulator::class));
        }, [ConsoleExtension::TAG_CONSOLE_COMMAND => [
            'name' => 'config:initialise',
        ]]);
        $container->register(ConfigExtendCommand::class, function (Container $container) {
            return new ConfigExtendCommand(
                $container->get(ConfigManipulator::class),
                $container->get(Registries::class),
            );
        }, [ConsoleExtension::TAG_CONSOLE_COMMAND => [
            'name' => 'config:extend',
        ]]);
    }

    private function registerConfig(Container $container): void
    {
        $container->register(ConfigManipulator::class, function (Container $container) {
            $cwd = $container->getParameter(self::PARAM_WORKING_DIR);
            $schemaPath = null;

            if (class_exists(Phar::class) && Phar::running() !== '') {
                $schemaPath = Path::makeRelative(__DIR__ . '/../../phpbench.schema.json', $cwd);
            }

            return new ConfigManipulator(
                $schemaPath,
                Path::join($cwd, 'phpbench.json'),
            );
        });
    }

    private function registerRegistries(Container $container): void
    {
        $container->register(Registries::class, function (Container $container) {
            $registries = [];

            foreach ($container->getServiceIdsForTag(self::TAG_REGISTRY) as $serviceId => $attrs) {
                if (!isset($attrs['name'])) {
                    throw new RuntimeException(sprintf(
                        'Service "%s" with tag "%s" must provide a `name` attribute',
                        $serviceId,
                        self::TAG_REGISTRY
                    ));
                }
                $registries[$attrs['name']] = $container->get($serviceId);
            }

            return new Registries($registries);
        });
    }
}
