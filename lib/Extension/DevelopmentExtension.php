<?php

namespace PhpBench\Extension;

use PhpBench\Console\Command\ConfigReferenceCommand;
use PhpBench\Console\Command\ConfigSchemaDumpCommand;
use PhpBench\Console\Command\ServiceOptionReferenceCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Development\ConfigDumper;
use PhpBench\Development\ConfigSchemaDumper;
use PhpBench\Development\OptionDumper;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DevelopmentExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(Container $container): void
    {
        $container->register(ConfigReferenceCommand::class, function (Container $container) {
            return new ConfigReferenceCommand(new ConfigDumper(
                $container->getExtensionClasses()
            ), $container->get(ConsoleExtension::SERVICE_OUTPUT_STD));
        }, [
            ConsoleExtension::TAG_CONSOLE_COMMAND => []
        ]);
        $container->register(ServiceOptionReferenceCommand::class, function (Container $container) {
            return new ServiceOptionReferenceCommand(new OptionDumper($container, [
                'generator' => ReportExtension::SERVICE_REGISTRY_GENERATOR,
                'renderer' => ReportExtension::SERVICE_REGISTRY_RENDERER,
                'component' => ReportExtension::SERVICE_REGISTRY_COMPONENT,
                'executor' => RunnerExtension::SERVICE_REGISTRY_EXECUTOR,
                'progress' => RunnerExtension::SERVICE_REGISTRY_LOGGER,
            ]), $container->get(ConsoleExtension::SERVICE_OUTPUT_ERR));
        }, [
            ConsoleExtension::TAG_CONSOLE_COMMAND => []
        ]);
        $container->register(ConfigSchemaDumpCommand::class, function (Container $container) {
            return new ConfigSchemaDumpCommand(new ConfigSchemaDumper(
                $container->getExtensionClasses()
            ), $container->get(ConsoleExtension::SERVICE_OUTPUT_STD));
        }, [
            ConsoleExtension::TAG_CONSOLE_COMMAND => []
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $resolver): void
    {
    }
}
