<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extension;

use PhpBench\Container;
use PhpBench\ProgressLogger\DotsProgressLogger;
use PhpBench\ProgressLoggerRegistry;
use PhpBench\Report\ReportManager;
use PhpBench\Console\Command\RunCommand;
use PhpBench\Console\Command\ReportCommand;
use PhpBench\Benchmark\CollectionBuilder;
use PhpBench\Benchmark\Runner;
use PhpBench\Console\Application;
use Symfony\Component\Finder\Finder;
use PhpBench\Report\Generator\CompositeGenerator;
use PhpBench\ExtensionInterface;
use PhpBench\Benchmark\Executor;
use PhpBench\Benchmark\BenchmarkBuilder;
use PhpBench\Benchmark\Telespector;
use PhpBench\Benchmark\Parser;
use PhpBench\Benchmark\Teleflector;
use PhpBench\Report\Generator\ConsoleTabularGenerator;
use PhpBench\Tabular\Formatter\Registry\ArrayRegistry;
use PhpBench\Tabular\Formatter\Format\PrintfFormat;
use PhpBench\Tabular\Formatter\Format\BalanceFormat;
use PhpBench\Tabular\Formatter\Format\NumberFormat;
use PhpBench\Tabular\Formatter;
use PhpBench\Tabular\Tabular;
use PhpBench\Tabular\TableBuilder;
use PhpBench\Tabular\Dom\XPathResolver;
use PhpBench\Report\Generator\ConsoleTabularCustomGenerator;

class CoreExtension implements ExtensionInterface
{
    public function configure(Container $container)
    {
        $container->register('console.application', function (Container $container) {
            $application = new Application();

            foreach (array_keys($container->getServiceIdsForTag('console.command')) as $serviceId) {
                $command = $container->get($serviceId);
                $application->add($command);
            }

            return $application;
        });
        $container->register('benchmark.runner', function (Container $container) {
            return new Runner(
                $container->get('benchmark.collection_builder'),
                $container->get('benchmark.executor'),
                $container->getParameter('config_path')
            );
        });
        $container->register('benchmark.executor', function (Container $container) {
            return new Executor(
                $container->get('benchmark.telespector')
            );
        });
        $container->register('benchmark.finder', function (Container $container) {
            return new Finder();
        });
        $container->register('benchmark.telespector', function (Container $container) {
            return new Telespector(
                $container->hasParameter('bootstrap') ? $container->getParameter('bootstrap') : null,
                $container->getParameter('config_path')
            );
        });
        $container->register('benchmark.teleflector', function (Container $container) {
            return new Teleflector($container->get('benchmark.telespector'));
        });
        $container->register('benchmark.benchmark_builder', function (Container $container) {
            return new BenchmarkBuilder(
                $container->get('benchmark.teleflector'),
                $container->get('benchmark.parser')
            );
        });
        $container->register('benchmark.parser', function (Container $container) {
            return new Parser();
        });
        $container->register('benchmark.collection_builder', function (Container $container) {
            return new CollectionBuilder(
                $container->get('benchmark.benchmark_builder'),
                $container->get('benchmark.finder'),
                dirname($container->getParameter('config_path'))
            );
        });
        $container->register('report.manager', function (Container $container) {
            return new ReportManager(
                $container->get('json_schema.validator')
            );
        });
        $container->register('progress_logger.registry', function (Container $container) {
            return new ProgressLoggerRegistry();
        });

        $this->registerJsonSchema($container);
        $this->registerTabular($container);
        $this->registerCommands($container);
        $this->registerProgressLoggers($container);
        $this->registerReportGenerators($container);

        $container->mergeParameters(array(
            'path' => null,
            'reports' => array(),
            'config_path' => null,
            'progress_logger_name' => 'benchdots',
        ));
    }

    public function build(Container $container)
    {
        foreach ($container->getServiceIdsForTag('progress_logger') as $serviceId => $attributes) {
            $progressLogger = $container->get($serviceId);
            $container->get('progress_logger.registry')->addProgressLogger($attributes['name'], $progressLogger);
        }

        foreach ($container->getServiceIdsForTag('report_generator') as $serviceId => $attributes) {
            $reportGenerator = $container->get($serviceId);
            $container->get('report.manager')->addGenerator($attributes['name'], $reportGenerator);
        }

        foreach ($container->getParameter('reports') as $reportName => $report) {
            $container->get('report.manager')->addReport($reportName, $report);
        }
    }

    private function registerJsonSchema(Container $container)
    {
        $container->register('json_schema.validator', function (Container $container) {
            return new \JsonSchema\Validator();
        });
    }

    private function registerCommands(Container $container)
    {
        $container->register('console.command.run', function (Container $container) {
            return new RunCommand(
                $container->get('benchmark.runner'),
                $container->get('report.manager'),
                $container->get('progress_logger.registry'),
                $container->getParameter('progress_logger_name'),
                $container->getParameter('path'),
                $container->getParameter('config_path')
            );
        }, array('console.command' => array()));

        $container->register('console.command.report', function (Container $container) {
            return new ReportCommand(
                $container->get('report.manager')
            );
        }, array('console.command' => array()));
    }

    private function registerProgressLoggers(Container $container)
    {
        $container->register('progress_logger.dots', function (Container $container) {
            return new DotsProgressLogger();
        }, array('progress_logger' => array('name' => 'dots')));

        $container->register('progress_logger.benchdots', function (Container $container) {
            return new DotsProgressLogger(true);
        }, array('progress_logger' => array('name' => 'benchdots')));
    }

    private function registerReportGenerators(Container $container)
    {
        $container->register('report_generator.tabular', function (Container $container) {
            return new ConsoleTabularGenerator($container->get('tabular'));
        }, array('report_generator' => array('name' => 'console_table')));
        $container->register('report_generator.tabular_custom', function (Container $container) {
            return new ConsoleTabularCustomGenerator(
                $container->get('tabular'),
                $container->getParameter('config_path')
            );
        }, array('report_generator' => array('name' => 'console_table_custom')));
        $container->register('report_generator.composite', function (Container $container) {
            return new CompositeGenerator($container->get('report.manager'));
        }, array('report_generator' => array('name' => 'composite')));
    }

    private function registerTabular(Container $container)
    {
        $container->register('tabular.xpath_resolver', function () {
            $resolver = new XPathResolver();
            $resolver->registerFunction('average', 'PhpBench\Report\Dom\functions\avg');
            $resolver->registerFunction('deviation', 'PhpBench\Report\Dom\functions\deviation');
            $resolver->registerFunction('min', 'PhpBench\Report\Dom\functions\min');
            $resolver->registerFunction('max', 'PhpBench\Report\Dom\functions\max');
            $resolver->registerFunction('median', 'PhpBench\Report\Dom\functions\median');
            $resolver->registerFunction('parameters_to_json', 'PhpBench\Report\Dom\functions\parameters_to_json');
            $resolver->registerFunction('class_name', 'PhpBench\Report\Dom\functions\class_name');

            return $resolver;
        });

        $container->register('tabular.table_builder', function (Container $container) {
            return new TableBuilder($container->get('tabular.xpath_resolver'));
        });

        $container->register('tabular.formatter.registry', function (Container $container) {
            $registry = new ArrayRegistry();
            $registry->register('printf', new PrintfFormat());
            $registry->register('balance', new BalanceFormat());
            $registry->register('number', new NumberFormat());

            return $registry;
        });


        $container->register('tabular.formatter', function (Container $container) {
            return new Formatter($container->get('tabular.formatter.registry'));
        });

        $container->register('tabular', function (Container $container) {
            return new Tabular(
                $container->get('tabular.table_builder'),
                $container->get('json_schema.validator'),
                $container->get('tabular.formatter')
            );
        });
    }
}
