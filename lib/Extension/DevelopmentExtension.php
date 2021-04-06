<?php

namespace PhpBench\Extension;

use PhpBench\Console\Command\ConfigReferenceCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Development\ConfigDumper;
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
            ), $container->get(CoreExtension::SERVICE_OUTPUT_STD));
        }, [
            CoreExtension::TAG_CONSOLE_COMMAND => []
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $resolver): void
    {
    }
}
