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
use PhpBench\Console\Command\Handler\DumpHandler;
use PhpBench\Console\Command\Handler\SuiteCollectionHandler;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Console\Command\LogCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Path\Path;
use PhpBench\Serializer\XmlDecoder;
use PhpBench\Serializer\XmlEncoder;
use PhpBench\Storage\Driver\Xml\XmlDriver;
use PhpBench\Storage\StorageRegistry;
use PhpBench\Storage\UuidResolver;
use PhpBench\Storage\UuidResolver\ChainResolver;
use PhpBench\Storage\UuidResolver\LatestResolver;
use PhpBench\Storage\UuidResolver\TagResolver;
use PhpBench\Util\TimeUnit;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StorageExtension implements ExtensionInterface
{
    public const PARAM_STORAGE = 'storage.driver';
    public const PARAM_XML_STORAGE_PATH = 'storage.xml_storage_path';
    public const PARAM_STORE_BINARY = 'storage.store_binary';

    public const SERVICE_REGISTRY_DRIVER = 'storage.driver_registry';

    public const TAG_STORAGE_DRIVER = 'storage.driver';
    public const TAG_UUID_RESOLVER = 'storage.uuid_resolver';

    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_STORAGE => 'xml',
            self::PARAM_XML_STORAGE_PATH => '.phpbench/storage',
            self::PARAM_STORE_BINARY => false,
        ]);

        $resolver->setAllowedTypes(self::PARAM_STORAGE, ['string']);
        $resolver->setAllowedTypes(self::PARAM_XML_STORAGE_PATH, ['string']);
        $resolver->setAllowedTypes(self::PARAM_STORE_BINARY, ['boolean']);
        SymfonyOptionsResolverCompat::setInfos($resolver, [
            self::PARAM_STORAGE => 'Storage driver to use',
            self::PARAM_XML_STORAGE_PATH => 'Path to store benchmark runs when they are stored with ``--store`` or ``--tag=foo``',
            self::PARAM_STORE_BINARY => 'If binary and serialized parameter values should be stored',
        ]);
    }

    public function load(Container $container): void
    {
        $this->registerCommands($container);
        $this->registerSerializer($container);
        $this->registerStorage($container);
    }

    private function registerCommands(Container $container): void
    {
        $container->register(SuiteCollectionHandler::class, function (Container $container) {
            return new SuiteCollectionHandler(
                $container->get(XmlDecoder::class),
                $container->get(self::SERVICE_REGISTRY_DRIVER),
                $container->get(UuidResolver::class)
            );
        });

        $container->register(DumpHandler::class, function (Container $container) {
            return new DumpHandler(
                $container->get(XmlEncoder::class),
                $container->get(ConsoleExtension::SERVICE_OUTPUT_STD)
            );
        });

        $container->register(LogCommand::class, function (Container $container) {
            return new LogCommand(
                $container->get(self::SERVICE_REGISTRY_DRIVER),
                $container->get(TimeUnit::class),
                $container->get(TimeUnitHandler::class),
                null,
                $container->get(ConsoleExtension::SERVICE_OUTPUT_STD)
            );
        }, [
            ConsoleExtension::TAG_CONSOLE_COMMAND => []
        ]);
    }

    private function registerSerializer(Container $container): void
    {
        $container->register(XmlEncoder::class, function (Container $container) {
            return new XmlEncoder($container->getParameter(self::PARAM_STORE_BINARY));
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
                Path::makeAbsolute(
                    $container->getParameter(self::PARAM_XML_STORAGE_PATH),
                    $container->getParameter(CoreExtension::PARAM_WORKING_DIR)
                ),
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
}
