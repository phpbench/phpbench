<?php

namespace PhpBench\Examples\Extension;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Examples\Extension\Command\CatsCommand;
use PhpBench\Examples\Extension\Executor\AcmeExecutor;
use PhpBench\Examples\Extension\ProgressLogger\CatLogger;
use PhpBench\Extension\CoreExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

// section: command_di,executor_di,progress_logger_di
class AcmeExtension implements ExtensionInterface
{
// endsection: executor_di,progress_logger_di
    private const PARAM_NUMBER_OF_CATS = 'acme.number_of_cats';

    // section: command_di
    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_NUMBER_OF_CATS => 7
        ]);
    }

    // endsection: command_di

    // section: command_di,executor_di
    public function load(Container $container): void
    {
        // endsection: executor_di
        $container->register(CatsCommand::class, function (Container $container) {
            return new CatsCommand($container->getParameter(self::PARAM_NUMBER_OF_CATS));
        }, [
            CoreExtension::TAG_CONSOLE_COMMAND => []
        ]);
        // endsection: command_di
        
        // section: progress_logger_di
        $container->register(CatLogger::class, function (Container $container) {
            return new CatLogger();
        }, [
            CoreExtension::TAG_PROGRESS_LOGGER => [
                'name' => 'cats',
            ]
        ]);
        // endsection: progress_logger_di

        // section: executor_di
        $container->register(AcmeExecutor::class, function (Container $container) {
            return new AcmeExecutor();
        }, [
            CoreExtension::TAG_EXECUTOR => [
                'name' => 'acme',
            ]
        ]);
    // section: command_di,progress_logger_di
    }
}
// endsection: command_di,executor_di,progress_logger_di
