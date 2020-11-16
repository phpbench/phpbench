<?php

namespace PhpBench\Examples\Extension;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Examples\Extension\Command\CatsCommand;
use PhpBench\Examples\Extension\Executor\AcmeExecutor;
use PhpBench\Extension\CoreExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

// section: command_di
class AcmeExtension implements ExtensionInterface
{
// endsection: command_di
    private const PARAM_NUMBER_OF_CATS = 'acme.number_of_cats';

    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_NUMBER_OF_CATS => 7
        ]);
    }

    // section: command_di
    public function load(Container $container): void
    {
        $container->register(CatsCommand::class, function (Container $container) {
            return new CatsCommand($container->getParameter(self::PARAM_NUMBER_OF_CATS));
        }, [
            CoreExtension::TAG_CONSOLE_COMMAND => []
        ]);
        // endsection: command_di

        $container->register(AcmeExecutor::class, function (Container $container) {
            return new AcmeExecutor();
        }, [
            CoreExtension::TAG_EXECUTOR => [
                'name' => 'acme',
            ]
        ]);
    // section: command_di
    }
}
// endsection: command_di
