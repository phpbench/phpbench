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

use Humbug\SelfUpdate\Updater;
use PhpBench\Console\Application;
use PhpBench\Console\Command\Handler\DumpHandler;
use PhpBench\Console\Command\Handler\ReportHandler;
use PhpBench\Console\Command\Handler\SuiteCollectionHandler;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Console\Command\LogCommand;
use PhpBench\Console\Command\SelfUpdateCommand;
use PhpBench\Console\Command\ShowCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Json\JsonDecoder;
use PhpBench\Logger\ConsoleLogger;
use PhpBench\PhpBench;
use PhpBench\Serializer\XmlDecoder;
use PhpBench\Serializer\XmlEncoder;
use PhpBench\Storage\Driver\Xml\XmlDriver;
use PhpBench\Storage\StorageRegistry;
use PhpBench\Storage\UuidResolver;
use PhpBench\Storage\UuidResolver\ChainResolver;
use PhpBench\Storage\UuidResolver\LatestResolver;
use PhpBench\Storage\UuidResolver\TagResolver;
use PhpBench\Util\TimeUnit;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoreExtension implements ExtensionInterface
{
    public const PARAM_CONFIG_PATH = 'config_path';
    public const PARAM_CONSOLE_ANSI = 'console.ansi';
    public const PARAM_CONSOLE_ERROR_STREAM = 'console.error_stream';
    public const PARAM_CONSOLE_OUTPUT_STREAM = 'console.output_stream';
    public const PARAM_DEBUG = 'debug';
    public const PARAM_DISABLE_OUTPUT = 'console.disable_output';
    public const PARAM_EXTENSIONS = 'extensions';
    public const PARAM_OUTPUT_MODE = 'output_mode';
    public const PARAM_STORAGE = 'storage';

    public const PARAM_TIME_UNIT = 'time_unit';
    public const PARAM_XML_STORAGE_PATH = 'xml_storage_path';

    public const SERVICE_OUTPUT_ERR = 'console.stream.err';
    public const SERVICE_OUTPUT_STD = 'console.stream.std';
    public const SERVICE_REGISTRY_LOGGER = 'progress_logger.registry';
    public const SERVICE_REGISTRY_DRIVER = 'storage.driver_registry';
    public const TAG_CONSOLE_COMMAND = 'console.command';
    public const TAG_PROGRESS_LOGGER = 'progress_logger';
    public const TAG_STORAGE_DRIVER = 'storage_driver';
    public const TAG_UUID_RESOLVER = 'uuid_resolver';

    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

            self::PARAM_CONSOLE_ANSI => true,
            self::PARAM_DISABLE_OUTPUT => false,
            self::PARAM_CONSOLE_OUTPUT_STREAM => 'php://stdout',
            self::PARAM_CONSOLE_ERROR_STREAM => 'php://stderr',
            self::PARAM_DEBUG => false,
            self::PARAM_EXTENSIONS => [],
            self::PARAM_OUTPUT_MODE => TimeUnit::MODE_TIME,
            self::PARAM_TIME_UNIT => TimeUnit::MICROSECONDS,
            self::PARAM_STORAGE => 'xml',
            self::PARAM_XML_STORAGE_PATH => '.phpbench/storage',

            self::PARAM_CONFIG_PATH => null,
        ]);

        $resolver->setAllowedTypes(self::PARAM_DEBUG, ['bool']);
        $resolver->setAllowedTypes(self::PARAM_CONFIG_PATH, ['string', 'null']);
        $resolver->setAllowedTypes(self::PARAM_CONSOLE_ANSI, ['bool']);
        $resolver->setAllowedTypes(self::PARAM_CONSOLE_ERROR_STREAM, ['string']);
        $resolver->setAllowedTypes(self::PARAM_CONSOLE_OUTPUT_STREAM, ['string']);
        $resolver->setAllowedTypes(self::PARAM_TIME_UNIT, ['string']);
        $resolver->setAllowedTypes(self::PARAM_OUTPUT_MODE, ['string']);
        $resolver->setAllowedTypes(self::PARAM_STORAGE, ['string']);
        $resolver->setAllowedTypes(self::PARAM_XML_STORAGE_PATH, ['string']);
        $resolver->setAllowedTypes(self::PARAM_DISABLE_OUTPUT, ['bool']);
        $resolver->setAllowedTypes(self::PARAM_CONSOLE_OUTPUT_STREAM, ['string']);
        $resolver->setAllowedTypes(self::PARAM_EXTENSIONS, ['array']);
    }

    public function load(Container $container): void
    {
        $container->register(self::SERVICE_OUTPUT_STD, function (Container $container) {
            return $this->createOutput($container, self::PARAM_CONSOLE_OUTPUT_STREAM);
        });

        $container->register(self::SERVICE_OUTPUT_ERR, function (Container $container) {
            return $this->createOutput($container, self::PARAM_CONSOLE_ERROR_STREAM);
        });

        $container->register(InputInterface::class, function (Container $container) {
            return new ArgvInput();
        });

        $container->register(Application::class, function (Container $container) {
            $application = new Application();

            foreach (array_keys($container->getServiceIdsForTag(self::TAG_CONSOLE_COMMAND)) as $serviceId) {
                $command = $container->get($serviceId);
                $application->add($command);
            }

            return $application;
        });

        $container->register(LoggerInterface::class, function (Container $container) {
            return new ConsoleLogger(
                $container->getParameter(self::PARAM_DEBUG)
            );
        });

        $container->register(TimeUnit::class, function (Container $container) {
            return new TimeUnit(TimeUnit::MICROSECONDS, $container->getParameter(self::PARAM_TIME_UNIT));
        });

        $this->registerJson($container);
        $this->registerCommands($container);
        $this->registerSerializer($container);
        $this->registerStorage($container);
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

        $container->register(SuiteCollectionHandler::class, function (Container $container) {
            return new SuiteCollectionHandler(
                $container->get(XmlDecoder::class),
                $container->get(self::SERVICE_REGISTRY_DRIVER),
                $container->get(UuidResolver::class)
            );
        });

        $container->register(DumpHandler::class, function (Container $container) {
            return new DumpHandler(
                $container->get(XmlEncoder::class)
            );
        });

        $container->register(LogCommand::class, function (Container $container) {
            return new LogCommand(
                $container->get(self::SERVICE_REGISTRY_DRIVER),
                $container->get(TimeUnit::class),
                $container->get(TimeUnitHandler::class)
            );
        }, [
            self::TAG_CONSOLE_COMMAND => []
        ]);

        $container->register(ShowCommand::class, function (Container $container) {
            return new ShowCommand(
                $container->get(self::SERVICE_REGISTRY_DRIVER),
                $container->get(ReportHandler::class),
                $container->get(TimeUnitHandler::class),
                $container->get(DumpHandler::class),
                $container->get(UuidResolver::class)
            );
        }, [
            self::TAG_CONSOLE_COMMAND => []
        ]);

        if (class_exists(Updater::class) && class_exists(\Phar::class) && \Phar::running()) {
            $container->register(SelfUpdateCommand::class, function (Container $container) {
                return new SelfUpdateCommand();
            }, [
                self::TAG_CONSOLE_COMMAND => []
            ]);
        }
    }

    private function registerSerializer(Container $container): void
    {
        $container->register(XmlEncoder::class, function (Container $container) {
            return new XmlEncoder();
        });
        $container->register(XmlDecoder::class, function (Container $container) {
            return new XmlDecoder();
        });
    }

    private function registerStorage(Container $container): void
    {
        $container->register(self::SERVICE_REGISTRY_DRIVER, function (Container $container) {
            $registry = new StorageRegistry($container, $container->getParameter(self::PARAM_STORAGE));

            foreach ($container->getServiceIdsForTag(self::TAG_STORAGE_DRIVER) as $serviceId => $attributes) {
                $registry->registerService($attributes['name'], $serviceId);
            }

            return $registry;
        });
        $container->register(XmlDriver::class, function (Container $container) {
            return new XmlDriver(
                PhpBench::normalizePath($container->getParameter(self::PARAM_XML_STORAGE_PATH)),
                $container->get(XmlEncoder::class),
                $container->get(XmlDecoder::class)
            );
        }, [self::TAG_STORAGE_DRIVER => ['name' => 'xml']]);

        $container->register(UuidResolver::class, function (Container $container) {
            $resolvers = [];

            foreach (array_keys($container->getServiceIdsForTag(self::TAG_UUID_RESOLVER)) as $serviceId) {
                $resolvers[] = $container->get($serviceId);
            }

            return new UuidResolver(new ChainResolver($resolvers));
        });

        $container->register(LatestResolver::class, function (Container $container) {
            return new LatestResolver(
                $container->get(self::SERVICE_REGISTRY_DRIVER)
            );
        }, [self::TAG_UUID_RESOLVER => []]);

        $container->register(TagResolver::class, function (Container $container) {
            return new TagResolver(
                $container->get(self::SERVICE_REGISTRY_DRIVER)
            );
        }, [self::TAG_UUID_RESOLVER => []]);
    }

    private function createOutput(Container $container, string $type): OutputInterface
    {
        if ($container->getParameter(self::PARAM_DISABLE_OUTPUT)) {
            return new NullOutput();
        }

        $output = (function (string $name): OutputInterface {
            $resource = fopen($name, 'w');

            if (false === $resource) {
                throw new RuntimeException(sprintf(
                    'Could not open stream "%s"',
                    $name
                ));
            }

            return new StreamOutput($resource);
        })($container->getParameter($type));

        if (false === $container->getParameter(self::PARAM_CONSOLE_ANSI)) {
            $output->setDecorated(false);
        }

        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('black', 'green', []));
        $output->getFormatter()->setStyle('baseline', new OutputFormatterStyle('cyan', null, []));
        $output->getFormatter()->setStyle('result-neutral', new OutputFormatterStyle('cyan', null, []));
        $output->getFormatter()->setStyle('result-good', new OutputFormatterStyle('green', null, []));
        $output->getFormatter()->setStyle('result-none', new OutputFormatterStyle(null, null, []));
        $output->getFormatter()->setStyle('result-failure', new OutputFormatterStyle('white', 'red', []));
        $output->getFormatter()->setStyle('title', new OutputFormatterStyle('white', null, ['bold']));
        $output->getFormatter()->setStyle('subtitle', new OutputFormatterStyle('white', null, []));
        $output->getFormatter()->setStyle('description', new OutputFormatterStyle(null, null, []));

        return $output;
    }
}
