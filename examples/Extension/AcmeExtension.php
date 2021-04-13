<?php

namespace PhpBench\Examples\Extension;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Examples\Extension\Command\CatsCommand;
use PhpBench\Examples\Extension\Executor\AcmeExecutor;
use PhpBench\Examples\Extension\ProgressLogger\CatLogger;
use PhpBench\Examples\Extension\Report\AcmeGenerator;
use PhpBench\Extension\ConsoleExtension;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extension\ReportExtension;
use PhpBench\Extension\RunnerExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

// section: all
class AcmeExtension implements ExtensionInterface
{
    // endsection: all
    // section: command_di
    private const PARAM_NUMBER_OF_CATS = 'acme.number_of_cats';
    // endsection: command_di

    // section: all
    public function configure(OptionsResolver $resolver): void
    {
        // endsection: all
        // section: command_di
        $resolver->setDefaults([
            self::PARAM_NUMBER_OF_CATS => 7
        ]);
        // endsection: command_di
        // section: all
    }

    // endsection: all
    // section: all
    public function load(Container $container): void
    {
        // endsection: all
        // section: command_di
        $container->register(CatsCommand::class, function (Container $container) {
            return new CatsCommand($container->getParameter(self::PARAM_NUMBER_OF_CATS));
        }, [
            ConsoleExtension::TAG_CONSOLE_COMMAND => []
        ]);
        // endsection: command_di
        
        // section: progress_logger_di
        $container->register(CatLogger::class, function (Container $container) {
            return new CatLogger(
                $container->get(ConsoleExtension::SERVICE_OUTPUT_ERR)
            );
        }, [
            RunnerExtension::TAG_PROGRESS_LOGGER => [
                'name' => 'cats',
            ]
        ]);
        // endsection: progress_logger_di

        // section: report_generator_di
        $container->register(AcmeGenerator::class, function (Container $container) {
            return new AcmeGenerator();
        }, [
            ReportExtension::TAG_REPORT_GENERATOR => [
                'name' => 'catordog',
            ]
        ]);
        // endsection: report_generator_di

        // section: executor_di
        $container->register(AcmeExecutor::class, function (Container $container) {
            return new AcmeExecutor();
        }, [
            RunnerExtension::TAG_EXECUTOR => [
                'name' => 'acme',
            ]
        ]);
        // endsection: executor_di
        // section: all
    }
}
// endsection: all
