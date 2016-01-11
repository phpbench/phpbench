<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extension;

use PhpBench\Benchmark\CollectionBuilder;
use PhpBench\Benchmark\Executor\DebugExecutor;
use PhpBench\Benchmark\Executor\MicrotimeExecutor;
use PhpBench\Benchmark\Metadata\Driver\AnnotationDriver;
use PhpBench\Benchmark\Metadata\Factory;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Remote\Reflector;
use PhpBench\Benchmark\Runner;
use PhpBench\Console\Application;
use PhpBench\Console\Command\Handler\ReportHandler;
use PhpBench\Console\Command\Handler\RunnerHandler;
use PhpBench\Console\Command\ReportCommand;
use PhpBench\Console\Command\RunCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Progress\Logger\BlinkenLogger;
use PhpBench\Progress\Logger\DotsLogger;
use PhpBench\Progress\Logger\NullLogger;
use PhpBench\Progress\Logger\TravisLogger;
use PhpBench\Progress\Logger\VerboseLogger;
use PhpBench\Progress\LoggerRegistry;
use PhpBench\Registry\Registry;
use PhpBench\Report\Generator\CompositeGenerator;
use PhpBench\Report\Generator\Tabular\Format\TimeFormat;
use PhpBench\Report\Generator\TabularCustomGenerator;
use PhpBench\Report\Generator\TabularGenerator;
use PhpBench\Report\Renderer\ConsoleRenderer;
use PhpBench\Report\Renderer\DebugRenderer;
use PhpBench\Report\Renderer\DelimitedRenderer;
use PhpBench\Report\Renderer\XsltRenderer;
use PhpBench\Report\ReportManager;
use PhpBench\Tabular\Definition\Expander;
use PhpBench\Tabular\Definition\Loader;
use PhpBench\Tabular\Dom\XPathResolver;
use PhpBench\Tabular\Formatter;
use PhpBench\Tabular\Formatter\Format\BalanceFormat;
use PhpBench\Tabular\Formatter\Format\JSONFormat;
use PhpBench\Tabular\Formatter\Format\NumberFormat;
use PhpBench\Tabular\Formatter\Format\PrintfFormat;
use PhpBench\Tabular\Formatter\Format\TruncateFormat;
use PhpBench\Tabular\Formatter\Registry\ArrayRegistry;
use PhpBench\Tabular\TableBuilder;
use PhpBench\Tabular\Tabular;
use PhpBench\Util\TimeUnit;
use Symfony\Component\Finder\Finder;

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
        $container->register('report.manager', function (Container $container) {
            return new ReportManager(
                $container->get('report.registry.generator'),
                $container->get('report.registry.renderer')
            );
        });

        $this->registerBenchmark($container);
        $this->registerJsonSchema($container);
        $this->registerTabular($container);
        $this->registerCommands($container);
        $this->registerRegistries($container);
        $this->registerProgressLoggers($container);
        $this->registerReportGenerators($container);
        $this->registerReportRenderers($container);

        $container->mergeParameters(array(
            'path' => null,
            'reports' => array(),
            'outputs' => array(),
            'executors' => array(),
            'config_path' => null,
            'progress' => getenv('CONTINUOUS_INTEGRATION') ? 'travis' : 'verbose',
            'retry_threshold' => null,
            'time_unit' => TimeUnit::MICROSECONDS,
            'output_mode' => TimeUnit::MODE_TIME,
        ));
    }

    public function build(Container $container)
    {
        foreach ($container->getServiceIdsForTag('progress_logger') as $serviceId => $attributes) {
            $progressLogger = $container->get($serviceId);
            $container->get('progress_logger.registry')->addProgressLogger($attributes['name'], $progressLogger);
        }

        foreach ($container->getServiceIdsForTag('report_generator') as $serviceId => $attributes) {
            $container->get('report.registry.generator')->registerService($attributes['name'], $serviceId);
        }

        foreach ($container->getServiceIdsForTag('report_renderer') as $serviceId => $attributes) {
            $container->get('report.registry.renderer')->registerService($attributes['name'], $serviceId);
        }

        foreach ($container->getServiceIdsForTag('benchmark_executor') as $serviceId => $attributes) {
            $container->get('benchmark.registry.executor')->registerService($attributes['name'], $serviceId);
        }

        $generatorConfigs = array_merge(
            require(__DIR__ . '/config/report/generators.php'),
            $container->getParameter('reports')
        );
        foreach ($generatorConfigs as $name => $config) {
            $container->get('report.registry.generator')->setConfig($name, $config);
        }

        $rendererConfigs = array_merge(
            require(__DIR__ . '/config/report/renderers.php'),
            $container->getParameter('outputs')
        );
        foreach ($rendererConfigs as $name => $config) {
            $container->get('report.registry.renderer')->setConfig($name, $config);
        }
        $executorConfigs = array_merge(
            require(__DIR__ . '/config/benchmark/executors.php'),
            $container->getParameter('executors')
        );
        foreach ($executorConfigs as $name => $config) {
            $container->get('benchmark.registry.executor')->setConfig($name, $config);
        }

        $this->relativizeConfigPath($container);
    }

    private function registerBenchmark(Container $container)
    {
        $container->register('benchmark.runner', function (Container $container) {
            return new Runner(
                $container->get('benchmark.collection_builder'),
                $container->get('benchmark.registry.executor'),
                $container->getParameter('retry_threshold'),
                $container->getParameter('config_path')
            );
        });

        $container->register('benchmark.executor.microtime', function (Container $container) {
            return new MicrotimeExecutor(
                $container->get('benchmark.remote.launcher')
            );
        }, array('benchmark_executor' => array('name' => 'microtime')));

        $container->register('benchmark.executor.debug', function (Container $container) {
            return new DebugExecutor(
                $container->get('benchmark.remote.launcher')
            );
        }, array('benchmark_executor' => array('name' => 'debug')));

        $container->register('benchmark.finder', function (Container $container) {
            return new Finder();
        });

        $container->register('benchmark.remote.launcher', function (Container $container) {
            return new Launcher(
                $container->hasParameter('bootstrap') ? $container->getParameter('bootstrap') : null
            );
        });

        $container->register('benchmark.remote.reflector', function (Container $container) {
            return new Reflector($container->get('benchmark.remote.launcher'));
        });

        $container->register('benchmark.metadata.driver.annotation', function (Container $container) {
            return new AnnotationDriver(
                $container->get('benchmark.remote.reflector')
            );
        });

        $container->register('benchmark.metadata_factory', function (Container $container) {
            return new Factory(
                $container->get('benchmark.remote.reflector'),
                $container->get('benchmark.metadata.driver.annotation')
            );
        });

        $container->register('benchmark.collection_builder', function (Container $container) {
            return new CollectionBuilder(
                $container->get('benchmark.metadata_factory'),
                $container->get('benchmark.finder')
            );
        });

        $container->register('benchmark.time_unit', function (Container $container) {
            return new TimeUnit(TimeUnit::MICROSECONDS, $container->getParameter('time_unit'));
        });
    }

    private function registerJsonSchema(Container $container)
    {
        $container->register('json_schema.validator', function (Container $container) {
            return new \JsonSchema\Validator();
        });
    }

    private function registerCommands(Container $container)
    {
        $container->register('console.command.handler.runner', function (Container $container) {
            return new RunnerHandler(
                $container->get('benchmark.runner'),
                $container->get('progress_logger.registry'),
                $container->get('benchmark.time_unit'),
                $container->getParameter('progress'),
                $container->getParameter('path')
            );
        });

        $container->register('console.command.handler.report', function (Container $container) {
            return new ReportHandler(
                $container->get('report.manager')
            );
        });

        $container->register('console.command.run', function (Container $container) {
            return new RunCommand(
                $container->get('console.command.handler.runner'),
                $container->get('console.command.handler.report')
            );
        }, array('console.command' => array()));

        $container->register('console.command.report', function (Container $container) {
            return new ReportCommand(
                $container->get('console.command.handler.report')
            );
        }, array('console.command' => array()));
    }

    private function registerProgressLoggers(Container $container)
    {
        $container->register('progress_logger.registry', function (Container $container) {
            return new LoggerRegistry();
        });

        $container->register('progress_logger.dots', function (Container $container) {
            return new DotsLogger($container->get('benchmark.time_unit'));
        }, array('progress_logger' => array('name' => 'dots')));

        $container->register('progress_logger.classdots', function (Container $container) {
            return new DotsLogger($container->get('benchmark.time_unit'), true);
        }, array('progress_logger' => array('name' => 'classdots')));

        $container->register('progress_logger.verbose', function (Container $container) {
            return new VerboseLogger($container->get('benchmark.time_unit'));
        }, array('progress_logger' => array('name' => 'verbose')));

        $container->register('progress_logger.travis', function (Container $container) {
            return new TravisLogger($container->get('benchmark.time_unit'));
        }, array('progress_logger' => array('name' => 'travis')));

        $container->register('progress_logger.null', function (Container $container) {
            return new NullLogger();
        }, array('progress_logger' => array('name' => 'none')));

        $container->register('progress_logger.blinken', function (Container $container) {
            return new BlinkenLogger($container->get('benchmark.time_unit'));
        }, array('progress_logger' => array('name' => 'blinken')));
    }

    private function registerReportGenerators(Container $container)
    {
        $container->register('report_generator.tabular', function (Container $container) {
            return new TabularGenerator(
                $container->get('tabular'),
                $container->get('tabular.definition_loader')
            );
        }, array('report_generator' => array('name' => 'table')));
        $container->register('report_generator.tabular_custom', function (Container $container) {
            return new TabularCustomGenerator(
                $container->get('tabular'),
                $container->get('tabular.definition_loader'),
                $container->getParameter('config_path')
            );
        }, array('report_generator' => array('name' => 'table_custom')));
        $container->register('report_generator.composite', function (Container $container) {
            return new CompositeGenerator($container->get('report.manager'));
        }, array('report_generator' => array('name' => 'composite')));
    }

    private function registerReportRenderers(Container $container)
    {
        $container->register('report_renderer.console', function (Container $container) {
            return new ConsoleRenderer();
        }, array('report_renderer' => array('name' => 'console')));
        $container->register('report_renderer.html', function (Container $container) {
            return new XsltRenderer();
        }, array('report_renderer' => array('name' => 'xslt')));
        $container->register('report_renderer.debug', function (Container $container) {
            return new DebugRenderer();
        }, array('report_renderer' => array('name' => 'debug')));
        $container->register('report_renderer.delimited', function (Container $container) {
            return new DelimitedRenderer();
        }, array('report_renderer' => array('name' => 'delimited')));
    }

    private function registerTabular(Container $container)
    {
        $container->register('tabular.xpath_resolver', function () {
            require_once __DIR__ . '/../Dom/xpath_functions.php';

            $resolver = new XPathResolver();
            $resolver->registerFunction('parameters_to_json', 'PhpBench\Dom\functions\parameters_to_json');
            $resolver->registerFunction('class_name', 'PhpBench\Dom\functions\class_name');
            $resolver->registerFunction('join_node_values', 'PhpBench\Dom\functions\join_node_values');
            $resolver->registerFunction('kde_mode', 'PhpBench\Dom\functions\join_node_values');
            $resolver->registerFunction('suite', 'PhpBench\Dom\functions\suite');

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
            $registry->register('truncate', new TruncateFormat());
            $registry->register('json_format', new JSONFormat());
            $registry->register('time', new TimeFormat($container->get('benchmark.time_unit')));

            return $registry;
        });

        $container->register('tabular.formatter', function (Container $container) {
            return new Formatter($container->get('tabular.formatter.registry'));
        });

        $container->register('tabular', function (Container $container) {
            return new Tabular(
                $container->get('tabular.table_builder'),
                $container->get('tabular.definition_loader'),
                $container->get('tabular.formatter'),
                $container->get('tabular.expander')
            );
        });

        $container->register('tabular.definition_loader', function (Container $container) {
            return new Loader(
                $container->get('json_schema.validator')
            );
        });

        $container->register('tabular.expander', function (Container $container) {
            return new Expander();
        });
    }

    private function registerRegistries(Container $container)
    {
        foreach (array('generator', 'renderer') as $registryType) {
            $container->register('report.registry.' . $registryType, function (Container $container) use ($registryType) {
                return new Registry(
                    $registryType,
                    $container,
                    $container->get('json_schema.validator')
                );
            });
        }

        $container->register('benchmark.registry.executor', function (Container $container) {
            return new Registry(
                'executor',
                $container,
                $container->get('json_schema.validator')
            );
        });
    }

    private function relativizeConfigPath(Container $container)
    {
        if (null === $path = $container->getParameter('path')) {
            return;
        }

        if (substr($path, 0, 1) === '/') {
            return;
        }

        $container->setParameter('path', sprintf('%s/%s', dirname($container->getParameter('config_path')), $path));
    }
}
