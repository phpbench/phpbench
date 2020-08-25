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
use PhpBench\Assertion\AsserterRegistry;
use PhpBench\Assertion\AssertionProcessor;
use PhpBench\Assertion\ComparatorAsserter;
use PhpBench\Benchmark\BaselineManager;
use PhpBench\Benchmark\BenchmarkFinder;
use PhpBench\Benchmark\Metadata\AnnotationReader;
use PhpBench\Benchmark\Metadata\Driver\AnnotationDriver;
use PhpBench\Benchmark\Metadata\MetadataFactory;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Remote\PayloadFactory;
use PhpBench\Benchmark\Remote\Reflector;
use PhpBench\Benchmark\Runner;
use PhpBench\Console\Application;
use PhpBench\Console\Command\ArchiveCommand;
use PhpBench\Console\Command\DeleteCommand;
use PhpBench\Console\Command\Handler\DumpHandler;
use PhpBench\Console\Command\Handler\ReportHandler;
use PhpBench\Console\Command\Handler\RunnerHandler;
use PhpBench\Console\Command\Handler\SuiteCollectionHandler;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Console\Command\LogCommand;
use PhpBench\Console\Command\ReportCommand;
use PhpBench\Console\Command\RunCommand;
use PhpBench\Console\Command\SelfUpdateCommand;
use PhpBench\Console\Command\ShowCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Environment\Provider;
use PhpBench\Environment\Supplier;
use PhpBench\Executor\Benchmark\DebugExecutor;
use PhpBench\Executor\Benchmark\MemoryCentricMicrotimeExecutor;
use PhpBench\Executor\Benchmark\MicrotimeExecutor;
use PhpBench\Executor\CompositeExecutor;
use PhpBench\Executor\Method\RemoteMethodExecutor;
use PhpBench\Formatter\Format\BalanceFormat;
use PhpBench\Formatter\Format\NumberFormat;
use PhpBench\Formatter\Format\PrintfFormat;
use PhpBench\Formatter\Format\TimeUnitFormat;
use PhpBench\Formatter\Format\TruncateFormat;
use PhpBench\Formatter\FormatRegistry;
use PhpBench\Formatter\Formatter;
use PhpBench\Json\JsonDecoder;
use PhpBench\Progress\Logger\BlinkenLogger;
use PhpBench\Progress\Logger\DotsLogger;
use PhpBench\Progress\Logger\HistogramLogger;
use PhpBench\Progress\Logger\NullLogger;
use PhpBench\Progress\Logger\TravisLogger;
use PhpBench\Progress\Logger\VerboseLogger;
use PhpBench\Progress\LoggerRegistry;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Registry\Registry;
use PhpBench\Report\Generator\CompositeGenerator;
use PhpBench\Report\Generator\EnvGenerator;
use PhpBench\Report\Generator\TableGenerator;
use PhpBench\Report\Renderer\ConsoleRenderer;
use PhpBench\Report\Renderer\DebugRenderer;
use PhpBench\Report\Renderer\DelimitedRenderer;
use PhpBench\Report\Renderer\XsltRenderer;
use PhpBench\Report\ReportManager;
use PhpBench\Serializer\XmlDecoder;
use PhpBench\Serializer\XmlEncoder;
use PhpBench\Storage;
use PhpBench\Storage\Driver\Xml\XmlDriver;
use PhpBench\Storage\StorageRegistry;
use PhpBench\Storage\UuidResolver\ChainResolver;
use PhpBench\Storage\UuidResolver\LatestResolver;
use PhpBench\Storage\UuidResolver\TagResolver;
use PhpBench\Util\TimeUnit;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\ExecutableFinder;

class CoreExtension implements ExtensionInterface
{
    const SERVICE_EXECUTOR_MICROTIME = 'benchmark.executor.microtime';
    const SERVICE_EXECUTOR_MEMORY = 'benchmark.executor.memory';
    const SERVICE_REMOTE_LAUNCHER = 'benchmark.remote.launcher';
    const SERVICE_EXECUTOR_METHOD_REMOTE = 'executor.method.remote_method';
    const SERVICE_EXECUTOR_BENCHMARK_MICROTIME = 'executor.benchmark.microtime';
    const TAG_EXECUTOR = 'benchmark_executor';


    public function getDefaultConfig()
    {
        return [
            'bootstrap' => null,
            'path' => null,
            'reports' => [],
            'outputs' => [],
            'executors' => [],
            'config_path' => null,
            'progress' => getenv('CONTINUOUS_INTEGRATION') ? 'travis' : 'verbose',
            'retry_threshold' => null,
            'time_unit' => TimeUnit::MICROSECONDS,
            'output_mode' => TimeUnit::MODE_TIME,
            'storage' => 'xml',
            'archiver' => 'xml',
            'subject_pattern' => '^bench',
            'archive_path' => '_archive',
            'env_baselines' => ['nothing', 'md5', 'file_rw'],
            'env_baseline_callables' => [],
            'xml_storage_path' => getcwd() . '/_storage', // use cwd because PHARs
            'extension_autoloader' => null,
            'php_config' => [],
            'php_binary' => null,
            'php_wrapper' => null,
            'php_disable_ini' => false,
            'annotation_import_use' => false,
        ];
    }

    public function load(Container $container)
    {
        $this->relativizeConfigPath($container);

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
        $this->registerJson($container);
        $this->registerCommands($container);
        $this->registerRegistries($container);
        $this->registerProgressLoggers($container);
        $this->registerReportGenerators($container);
        $this->registerReportRenderers($container);
        $this->registerEnvironment($container);
        $this->registerSerializer($container);
        $this->registerStorage($container);
        $this->registerFormatter($container);
        $this->registerAsserters($container);
    }

    private function registerBenchmark(Container $container)
    {
        $container->register('benchmark.runner', function (Container $container) {
            return new Runner(
                $container->get('benchmark.benchmark_finder'),
                $container->get('benchmark.registry.executor'),
                $container->get('environment.supplier'),
                $container->get('assertion.assertion_processor'),
                $container->getParameter('retry_threshold'),
                $container->getParameter('config_path')
            );
        });

        $container->register(self::SERVICE_EXECUTOR_MICROTIME, function (Container $container) {
            return new CompositeExecutor(
                $container->get(self::SERVICE_EXECUTOR_BENCHMARK_MICROTIME),
                $container->get(self::SERVICE_EXECUTOR_METHOD_REMOTE)
            );
        }, [self::TAG_EXECUTOR => ['name' => 'microtime']]);

        $container->register(self::SERVICE_EXECUTOR_MEMORY, function (Container $container) {
            return new CompositeExecutor(
                new MemoryCentricMicrotimeExecutor($container->get(self::SERVICE_REMOTE_LAUNCHER)),
                $container->get(self::SERVICE_EXECUTOR_METHOD_REMOTE)
            );
        }, [self::TAG_EXECUTOR => ['name' => 'memory_centric_microtime']]);

        $container->register(self::SERVICE_EXECUTOR_BENCHMARK_MICROTIME, function (Container $container) {
            return new MicrotimeExecutor(
                $container->get(self::SERVICE_REMOTE_LAUNCHER)
            );
        });

        $container->register(self::SERVICE_EXECUTOR_METHOD_REMOTE, function (Container $container) {
            return new RemoteMethodExecutor(
                $container->get(self::SERVICE_REMOTE_LAUNCHER)
            );
        });

        $container->register('benchmark.executor.debug', function (Container $container) {
            return new DebugExecutor();
        }, [self::TAG_EXECUTOR => ['name' => 'debug']]);

        $container->register('benchmark.finder', function (Container $container) {
            return new Finder();
        });
        $container->register(self::SERVICE_REMOTE_LAUNCHER, function (Container $container) {
            return new Launcher(
                new PayloadFactory(),
                new ExecutableFinder(),
                $container->hasParameter('bootstrap') ? $container->getParameter('bootstrap') : null,
                $container->hasParameter('php_binary') ? $container->getParameter('php_binary') : null,
                $container->hasParameter('php_config') ? $container->getParameter('php_config') : null,
                $container->hasParameter('php_wrapper') ? $container->getParameter('php_wrapper') : null,
                $container->hasParameter('php_disable_ini') ? $container->getParameter('php_disable_ini') : false
            );
        });

        $container->register('benchmark.remote.reflector', function (Container $container) {
            return new Reflector($container->get(self::SERVICE_REMOTE_LAUNCHER));
        });

        $container->register('benchmark.annotation_reader', function (Container $container) {
            return new AnnotationReader($container->getParameter('annotation_import_use'));
        });

        $container->register('benchmark.metadata.driver.annotation', function (Container $container) {
            return new AnnotationDriver(
                $container->get('benchmark.remote.reflector'),
                $container->getParameter('subject_pattern'),
                $container->get('benchmark.annotation_reader')
            );
        });

        $container->register('benchmark.metadata_factory', function (Container $container) {
            return new MetadataFactory(
                $container->get('benchmark.remote.reflector'),
                $container->get('benchmark.metadata.driver.annotation')
            );
        });

        $container->register('benchmark.benchmark_finder', function (Container $container) {
            return new BenchmarkFinder(
                $container->get('benchmark.metadata_factory')
            );
        });

        $container->register('benchmark.baseline_manager', function (Container $container) {
            $manager = new BaselineManager();
            $callables = array_merge([
                'nothing' => '\PhpBench\Benchmark\Baseline\Baselines::nothing',
                'md5' => '\PhpBench\Benchmark\Baseline\Baselines::md5',
                'file_rw' => '\PhpBench\Benchmark\Baseline\Baselines::fwriteFread',
            ], $container->getParameter('env_baseline_callables'));

            foreach ($callables as $name => $callable) {
                $manager->addBaselineCallable($name, $callable);
            }

            return $manager;
        });

        $container->register('benchmark.time_unit', function (Container $container) {
            return new TimeUnit(TimeUnit::MICROSECONDS, $container->getParameter('time_unit'));
        });
    }

    private function registerJson(Container $container)
    {
        $container->register('json.decoder', function (Container $container) {
            return new JsonDecoder();
        });
    }

    private function registerCommands(Container $container)
    {
        $container->register('console.command.handler.runner', function (Container $container) {
            return new RunnerHandler(
                $container->get('benchmark.runner'),
                $container->get('progress_logger.registry'),
                $container->getParameter('progress'),
                $container->getParameter('path')
            );
        });

        $container->register('console.command.handler.report', function (Container $container) {
            return new ReportHandler(
                $container->get('report.manager')
            );
        });

        $container->register('console.command.handler.time_unit', function (Container $container) {
            return new TimeUnitHandler(
                $container->get('benchmark.time_unit')
            );
        });

        $container->register('console.command.handler.suite_collection', function (Container $container) {
            return new SuiteCollectionHandler(
                $container->get('serializer.decoder.xml'),
                $container->get('storage.driver_registry'),
                $container->get('storage.uuid_resolver')
            );
        });

        $container->register('console.command.handler.dump', function (Container $container) {
            return new DumpHandler(
                $container->get('serializer.encoder.xml')
            );
        });

        $container->register('console.command.run', function (Container $container) {
            return new RunCommand(
                $container->get('console.command.handler.runner'),
                $container->get('console.command.handler.report'),
                $container->get('console.command.handler.suite_collection'),
                $container->get('console.command.handler.time_unit'),
                $container->get('console.command.handler.dump'),
                $container->get('storage.driver_registry')
            );
        }, ['console.command' => []]);

        $container->register('console.command.report', function (Container $container) {
            return new ReportCommand(
                $container->get('console.command.handler.report'),
                $container->get('console.command.handler.time_unit'),
                $container->get('console.command.handler.suite_collection'),
                $container->get('console.command.handler.dump')
            );
        }, ['console.command' => []]);

        $container->register('console.command.log', function (Container $container) {
            return new LogCommand(
                $container->get('storage.driver_registry'),
                $container->get('benchmark.time_unit'),
                $container->get('console.command.handler.time_unit')
            );
        }, ['console.command' => []]);

        $container->register('console.command.show', function (Container $container) {
            return new ShowCommand(
                $container->get('storage.driver_registry'),
                $container->get('console.command.handler.report'),
                $container->get('console.command.handler.time_unit'),
                $container->get('console.command.handler.dump'),
                $container->get('storage.uuid_resolver')
            );
        }, ['console.command' => []]);

        $container->register('console.command.archive', function (Container $container) {
            return new ArchiveCommand(
                $container->get('storage.archiver_registry')
            );
        }, ['console.command' => []]);

        $container->register('console.command.delete', function (Container $container) {
            return new DeleteCommand(
                $container->get('console.command.handler.suite_collection'),
                $container->get('storage.driver_registry')
            );
        }, ['console.command' => []]);

        if (class_exists(Updater::class) && \Phar::running()) {
            $container->register('console.command.self_update', function (Container $container) {
                return new SelfUpdateCommand();
            }, ['console.command' => []]);
        }
    }

    private function registerProgressLoggers(Container $container)
    {
        $container->register('progress_logger.registry', function (Container $container) {
            $registry = new LoggerRegistry();

            foreach ($container->getServiceIdsForTag('progress_logger') as $serviceId => $attributes) {
                $registry->addProgressLogger(
                    $attributes['name'],
                    $container->get($serviceId)
                );
            }

            return $registry;
        });

        $container->register('progress_logger.dots', function (Container $container) {
            return new DotsLogger($container->get('benchmark.time_unit'));
        }, ['progress_logger' => ['name' => 'dots']]);

        $container->register('progress_logger.classdots', function (Container $container) {
            return new DotsLogger($container->get('benchmark.time_unit'), true);
        }, ['progress_logger' => ['name' => 'classdots']]);

        $container->register('progress_logger.verbose', function (Container $container) {
            return new VerboseLogger($container->get('benchmark.time_unit'));
        }, ['progress_logger' => ['name' => 'verbose']]);

        $container->register('progress_logger.travis', function (Container $container) {
            return new TravisLogger($container->get('benchmark.time_unit'));
        }, ['progress_logger' => ['name' => 'travis']]);

        $container->register('progress_logger.null', function (Container $container) {
            return new NullLogger();
        }, ['progress_logger' => ['name' => 'none']]);

        $container->register('progress_logger.blinken', function (Container $container) {
            return new BlinkenLogger($container->get('benchmark.time_unit'));
        }, ['progress_logger' => ['name' => 'blinken']]);

        $container->register('progress_logger.histogram', function (Container $container) {
            return new HistogramLogger($container->get('benchmark.time_unit'));
        }, ['progress_logger' => ['name' => 'histogram']]);
    }

    private function registerReportGenerators(Container $container)
    {
        $container->register('report_generator.table', function (Container $container) {
            return new TableGenerator();
        }, ['report_generator' => ['name' => 'table']]);
        $container->register('report_generator.env', function (Container $container) {
            return new EnvGenerator();
        }, ['report_generator' => ['name' => 'env']]);
        $container->register('report_generator.composite', function (Container $container) {
            return new CompositeGenerator(
                $container->get('report.manager')
            );
        }, ['report_generator' => ['name' => 'composite']]);
    }

    private function registerReportRenderers(Container $container)
    {
        $container->register('report_renderer.console', function (Container $container) {
            return new ConsoleRenderer($container->get('phpbench.formatter'));
        }, ['report_renderer' => ['name' => 'console']]);
        $container->register('report_renderer.html', function (Container $container) {
            return new XsltRenderer($container->get('phpbench.formatter'));
        }, ['report_renderer' => ['name' => 'xslt']]);
        $container->register('report_renderer.debug', function (Container $container) {
            return new DebugRenderer();
        }, ['report_renderer' => ['name' => 'debug']]);
        $container->register('report_renderer.delimited', function (Container $container) {
            return new DelimitedRenderer();
        }, ['report_renderer' => ['name' => 'delimited']]);
    }

    private function registerFormatter(Container $container)
    {
        $container->register('phpbench.formatter.registry', function (Container $container) {
            $registry = new FormatRegistry();
            $registry->register('printf', new PrintfFormat());
            $registry->register('balance', new BalanceFormat());
            $registry->register('number', new NumberFormat());
            $registry->register('truncate', new TruncateFormat());
            $registry->register('time', new TimeUnitFormat($container->get('benchmark.time_unit')));

            return $registry;
        });

        $container->register('phpbench.formatter', function (Container $container) {
            $formatter = new Formatter($container->get('phpbench.formatter.registry'));
            $formatter->classesFromFile(__DIR__ . '/config/class/main.json');

            return $formatter;
        });
    }

    private function registerAsserters(Container $container)
    {
        $container->register('assertion.assertion_processor', function () use ($container) {
            return new AssertionProcessor(
                $container->get('assertion.registry'),
                $container->get('json.decoder')
            );
        });
        $container->register('assertion.asserter.comparator', function (Container $container) {
            return new ComparatorAsserter($container->get('benchmark.time_unit'));
        }, ['assertion.asserter' => ['name' => 'comparator']]);
    }

    private function registerRegistries(Container $container)
    {
        foreach (['generator' => 'reports', 'renderer' => 'outputs'] as $registryType => $optionName) {
            $container->register('report.registry.' . $registryType, function (Container $container) use ($registryType, $optionName) {
                $registry = new ConfigurableRegistry(
                    $registryType,
                    $container,
                    $container->get('json.decoder')
                );

                foreach ($container->getServiceIdsForTag('report_' . $registryType) as $serviceId => $attributes) {
                    $registry->registerService($attributes['name'], $serviceId);
                }

                $configs = array_merge(
                    require(__DIR__ . '/config/report/' . $registryType . 's.php'),
                    $container->getParameter($optionName)
                );

                foreach ($configs as $name => $config) {
                    $registry->setConfig($name, $config);
                }

                return $registry;
            });
        }

        $container->register('benchmark.registry.executor', function (Container $container) {
            $registry = new ConfigurableRegistry(
                'executor',
                $container,
                $container->get('json.decoder')
            );

            foreach ($container->getServiceIdsForTag(self::TAG_EXECUTOR) as $serviceId => $attributes) {
                $registry->registerService($attributes['name'], $serviceId);
            }

            $executorConfigs = array_merge(
                require(__DIR__ . '/config/benchmark/executors.php'),
                $container->getParameter('executors')
            );

            foreach ($executorConfigs as $name => $config) {
                $registry->setConfig($name, $config);
            }

            return $registry;
        });

        $container->register('assertion.registry', function (Container $container) {
            $registry = new AsserterRegistry($container);

            foreach ($container->getServiceIdsForTag('assertion.asserter') as $serviceId => $attributes) {
                $registry->registerService($attributes['name'], $serviceId);
            }

            return $registry;
        });
    }

    public function registerEnvironment(Container $container)
    {
        $container->register('environment.provider.uname', function (Container $container) {
            return new Provider\Uname();
        }, ['environment_provider' => []]);

        $container->register('environment.provider.php', function (Container $container) {
            return new Provider\Php(
                $container->get(self::SERVICE_REMOTE_LAUNCHER)
            );
        }, ['environment_provider' => []]);

        $container->register('environment.provider.opcache', function (Container $container) {
            return new Provider\Opcache(
                $container->get(self::SERVICE_REMOTE_LAUNCHER)
            );
        }, ['environment_provider' => []]);

        $container->register('environment.provider.unix_sysload', function (Container $container) {
            return new Provider\UnixSysload();
        }, ['environment_provider' => []]);

        $container->register('environment.provider.git', function (Container $container) {
            return new Provider\Git();
        }, ['environment_provider' => []]);

        $container->register('environment.provider.baseline', function (Container $container) {
            return new Provider\Baseline(
                $container->get('benchmark.baseline_manager'),
                $container->getParameter('env_baselines')
            );
        }, ['environment_provider' => []]);

        $container->register('environment.supplier', function (Container $container) {
            $supplier = new Supplier();

            foreach ($container->getServiceIdsForTag('environment_provider') as $serviceId => $attributes) {
                $provider = $container->get($serviceId);
                $supplier->addProvider($provider);
            }

            return $supplier;
        });
    }

    private function registerSerializer(Container $container)
    {
        $container->register('serializer.encoder.xml', function (Container $container) {
            return new XmlEncoder();
        });
        $container->register('serializer.decoder.xml', function (Container $container) {
            return new XmlDecoder();
        });
    }

    private function registerStorage(Container $container)
    {
        $container->register('storage.driver_registry', function (Container $container) {
            $registry = new StorageRegistry($container, $container->getParameter('storage'));

            foreach ($container->getServiceIdsForTag('storage_driver') as $serviceId => $attributes) {
                $registry->registerService($attributes['name'], $serviceId);
            }

            return $registry;
        });
        $container->register('storage.archiver_registry', function (Container $container) {
            $registry = new Registry('archiver', $container, $container->getParameter('archiver'));

            foreach ($container->getServiceIdsForTag('storage_archiver') as $serviceId => $attributes) {
                $registry->registerService($attributes['name'], $serviceId);
            }

            return $registry;
        });
        $container->register('storage.driver.xml', function (Container $container) {
            return new XmlDriver(
                $container->getParameter('xml_storage_path'),
                $container->get('serializer.encoder.xml'),
                $container->get('serializer.decoder.xml')
            );
        }, ['storage_driver' => ['name' => 'xml']]);

        $container->register('storage.uuid_resolver', function (Container $container) {
            $resolvers = [];

            foreach (array_keys($container->getServiceIdsForTag('uuid_resolver')) as $serviceId) {
                $resolvers[] = $container->get($serviceId);
            }

            return new ChainResolver($resolvers);
        });

        $container->register('storage.uuid_resolver.latest', function (Container $container) {
            return new LatestResolver(
                $container->get('storage.driver_registry')
            );
        }, ['uuid_resolver' => []]);

        $container->register('storage.uuid_resolver.tag', function (Container $container) {
            return new TagResolver(
                $container->get('storage.driver_registry')
            );
        }, ['uuid_resolver' => []]);

        $container->register('storage.archiver.xml', function (Container $container) {
            return new Storage\Archiver\XmlArchiver(
                $container->get('storage.driver_registry'),
                $container->get('serializer.encoder.xml'),
                $container->get('serializer.decoder.xml'),
                $container->getParameter('archive_path')
            );
        }, ['storage_archiver' => ['name' => 'xml']]);
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
