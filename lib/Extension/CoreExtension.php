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
use PhpBench\Assertion\AssertionProcessor;
use PhpBench\Assertion\ExpressionEvaluatorFactory;
use PhpBench\Assertion\ExpressionParser;
use PhpBench\Benchmark\BaselineManager;
use PhpBench\Benchmark\BenchmarkFinder;
use PhpBench\Benchmark\Metadata\AnnotationReader;
use PhpBench\Benchmark\Metadata\Driver\AnnotationDriver;
use PhpBench\Benchmark\Metadata\MetadataFactory;
use PhpBench\Benchmark\Runner;
use PhpBench\Console\Application;
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
use PhpBench\Executor\Benchmark\LocalExecutor;
use PhpBench\Executor\Benchmark\MemoryCentricMicrotimeExecutor;
use PhpBench\Executor\Benchmark\RemoteExecutor;
use PhpBench\Executor\CompositeExecutor;
use PhpBench\Executor\Method\ErrorHandlingExecutorDecorator;
use PhpBench\Executor\Method\LocalMethodExecutor;
use PhpBench\Executor\Method\RemoteMethodExecutor;
use PhpBench\Formatter\Format\BalanceFormat;
use PhpBench\Formatter\Format\InvertOnThroughputFormat;
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
use PhpBench\Reflection\RemoteReflector;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Remote\Launcher;
use PhpBench\Remote\PayloadFactory;
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
use PhpBench\Storage\Driver\Xml\XmlDriver;
use PhpBench\Storage\StorageRegistry;
use PhpBench\Storage\UuidResolver;
use PhpBench\Storage\UuidResolver\ChainResolver;
use PhpBench\Storage\UuidResolver\LatestResolver;
use PhpBench\Storage\UuidResolver\TagResolver;
use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\ExecutableFinder;
use Webmozart\PathUtil\Path;

class CoreExtension implements ExtensionInterface
{
    public const PARAM_ANNOTATION_IMPORT_USE = 'annotation_import_use';
    public const PARAM_BOOTSTRAP = 'bootstrap';
    public const PARAM_CONFIG_PATH = 'config_path';
    public const PARAM_ENV_BASELINES = 'env_baselines';
    public const PARAM_ENV_BASELINE_CALLABLES = 'env_baseline_callables';
    public const PARAM_EXECUTORS = 'executors';
    public const PARAM_OUTPUTS = 'outputs';
    public const PARAM_OUTPUT_MODE = 'output_mode';
    public const PARAM_PATH = 'path';
    public const PARAM_PHP_BINARY = 'php_binary';
    public const PARAM_PHP_CONFIG = 'php_config';
    public const PARAM_PHP_DISABLE_INI = 'php_disable_ini';
    public const PARAM_PHP_WRAPPER = 'php_wrapper';
    public const PARAM_PROGRESS = 'progress';
    public const PARAM_REPORTS = 'reports';
    public const PARAM_RETRY_THRESHOLD = 'retry_threshold';
    public const PARAM_STORAGE = 'storage';
    public const PARAM_SUBJECT_PATTERN = 'subject_pattern';
    public const PARAM_TIME_UNIT = 'time_unit';
    public const PARAM_XML_STORAGE_PATH = 'xml_storage_path';
    public const PARAM_REMOTE_SCRIPT_PATH = 'remote_script_path';
    public const PARAM_REMOTE_SCRIPT_REMOVE = 'remote_script_remove';
    public const PARAM_DISABLE_OUTPUT = 'console.disable_output';

    public const TAG_EXECUTOR = 'benchmark_executor';
    public const TAG_CONSOLE_COMMAND = 'console.command';
    public const TAG_ENV_PROVIDER = 'environment_provider';
    public const TAG_PROGRESS_LOGGER = 'progress_logger';
    public const TAG_REPORT_GENERATOR = 'report_generator';
    public const TAG_REPORT_RENDERER = 'report_renderer';
    public const TAG_STORAGE_DRIVER = 'storage_driver';
    public const TAG_UUID_RESOLVER = 'uuid_resolver';

    private const SERVICE_REGISTRY_DRIVER = 'storage.driver_registry';
    private const SERVICE_REGISTRY_EXECUTOR = 'benchmark.registry.executor';
    private const SERVICE_REGISTRY_GENERATOR = 'report.registry.generator';
    private const SERVICE_REGISTRY_LOGGER = 'progress_logger.registry';
    private const SERVICE_REGISTRY_RENDERER = 'report.registry.renderer';

    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_BOOTSTRAP => null,
            self::PARAM_PATH => null,
            self::PARAM_REPORTS => [],
            self::PARAM_OUTPUTS => [],
            self::PARAM_EXECUTORS => [],
            self::PARAM_CONFIG_PATH => null,
            self::PARAM_PROGRESS => getenv('CONTINUOUS_INTEGRATION') ? 'travis' : 'verbose',
            self::PARAM_RETRY_THRESHOLD => null,
            self::PARAM_TIME_UNIT => TimeUnit::MICROSECONDS,
            self::PARAM_OUTPUT_MODE => TimeUnit::MODE_TIME,
            self::PARAM_STORAGE => 'xml',
            self::PARAM_SUBJECT_PATTERN => '^bench',
            self::PARAM_ENV_BASELINES => ['nothing', 'md5', 'file_rw'],
            self::PARAM_ENV_BASELINE_CALLABLES => [],
            self::PARAM_XML_STORAGE_PATH => getcwd() . '/.phpbench/storage', // use cwd because PHARs
            self::PARAM_PHP_CONFIG => [],
            self::PARAM_PHP_BINARY => null,
            self::PARAM_PHP_WRAPPER => null,
            self::PARAM_PHP_DISABLE_INI => false,
            self::PARAM_ANNOTATION_IMPORT_USE => false,
            self::PARAM_REMOTE_SCRIPT_PATH => null,
            self::PARAM_REMOTE_SCRIPT_REMOVE => true,
            self::PARAM_DISABLE_OUTPUT => false,
        ]);
        $resolver->setAllowedTypes(self::PARAM_BOOTSTRAP, ['string', 'null']);
        $resolver->setAllowedTypes(self::PARAM_PATH, ['string', 'array', 'null']);
        $resolver->setAllowedTypes(self::PARAM_REPORTS, ['array']);
        $resolver->setAllowedTypes(self::PARAM_OUTPUTS, ['array']);
        $resolver->setAllowedTypes(self::PARAM_EXECUTORS, ['array']);
        $resolver->setAllowedTypes(self::PARAM_PROGRESS, ['string']);
        $resolver->setAllowedTypes(self::PARAM_RETRY_THRESHOLD, ['null', 'int', 'float']);
        $resolver->setAllowedTypes(self::PARAM_TIME_UNIT, ['string']);
        $resolver->setAllowedTypes(self::PARAM_OUTPUT_MODE, ['string']);
        $resolver->setAllowedTypes(self::PARAM_STORAGE, ['string']);
        $resolver->setAllowedTypes(self::PARAM_SUBJECT_PATTERN, ['string']);
        $resolver->setAllowedTypes(self::PARAM_ENV_BASELINES, ['array']);
        $resolver->setAllowedTypes(self::PARAM_ENV_BASELINE_CALLABLES, ['array']);
        $resolver->setAllowedTypes(self::PARAM_XML_STORAGE_PATH, ['string']);
        $resolver->setAllowedTypes(self::PARAM_PHP_CONFIG, ['array']);
        $resolver->setAllowedTypes(self::PARAM_PHP_BINARY, ['string', 'null']);
        $resolver->setAllowedTypes(self::PARAM_PHP_WRAPPER, ['string', 'null']);
        $resolver->setAllowedTypes(self::PARAM_PHP_DISABLE_INI, ['bool']);
        $resolver->setAllowedTypes(self::PARAM_ANNOTATION_IMPORT_USE, ['bool']);
        $resolver->setAllowedTypes(self::PARAM_REMOTE_SCRIPT_REMOVE, ['bool']);
        $resolver->setAllowedTypes(self::PARAM_REMOTE_SCRIPT_PATH, ['string', 'null']);
        $resolver->setAllowedTypes(self::PARAM_DISABLE_OUTPUT, ['bool']);
    }

    public function load(Container $container): void
    {
        $this->relativizeConfigPath($container);

        $container->register(OutputInterface::class, function (Container $container) {
            if ($container->getParameter(self::PARAM_DISABLE_OUTPUT)) {
                return new NullOutput();
            }

            $output = new ConsoleOutput();
            $output->getFormatter()->setStyle('success', new OutputFormatterStyle('black', 'green', []));
            $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow', []));

            return $output;
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
        $container->register(ReportManager::class, function (Container $container) {
            return new ReportManager(
                $container->get(self::SERVICE_REGISTRY_GENERATOR),
                $container->get(self::SERVICE_REGISTRY_RENDERER)
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

    private function registerBenchmark(Container $container): void
    {
        $container->register(Runner::class, function (Container $container) {
            return new Runner(
                $container->get(self::SERVICE_REGISTRY_EXECUTOR),
                $container->get(Supplier::class),
                $container->get(AssertionProcessor::class),
                $container->getParameter(self::PARAM_RETRY_THRESHOLD),
                $container->getParameter(self::PARAM_CONFIG_PATH)
            );
        });

        $container->register(RemoteExecutor::class . '.composite', function (Container $container) {
            return new CompositeExecutor(
                $container->get(RemoteExecutor::class),
                new ErrorHandlingExecutorDecorator($container->get(RemoteMethodExecutor::class))
            );
        }, [self::TAG_EXECUTOR => ['name' => 'remote']]);

        $container->register(LocalExecutor::class . '.composite', function (Container $container) {
            return new CompositeExecutor(
                $container->get(LocalExecutor::class),
                new ErrorHandlingExecutorDecorator($container->get(LocalMethodExecutor::class))
            );
        }, [self::TAG_EXECUTOR => ['name' => 'local']]);

        $container->register(MemoryCentricMicrotimeExecutor::class, function (Container $container) {
            return new CompositeExecutor(
                new MemoryCentricMicrotimeExecutor($container->get(Launcher::class)),
                $container->get(RemoteMethodExecutor::class)
            );
        }, [self::TAG_EXECUTOR => ['name' => 'memory_centric_microtime']]);

        $container->register(RemoteExecutor::class, function (Container $container) {
            return new RemoteExecutor(
                $container->get(Launcher::class)
            );
        });

        $container->register(LocalExecutor::class, function (Container $container) {
            return new LocalExecutor();
        });

        $container->register(RemoteMethodExecutor::class, function (Container $container) {
            return new RemoteMethodExecutor(
                $container->get(Launcher::class)
            );
        });

        $container->register(LocalMethodExecutor::class, function (Container $container) {
            return new LocalMethodExecutor();
        });

        $container->register(DebugExecutor::class, function (Container $container) {
            return new DebugExecutor();
        }, [
            self::TAG_EXECUTOR => ['name' => 'debug']
        ]);

        $container->register(Finder::class, function (Container $container) {
            return new Finder();
        });

        $container->register(Launcher::class, function (Container $container) {
            return new Launcher(
                new PayloadFactory(
                    $container->getParameter(self::PARAM_REMOTE_SCRIPT_PATH),
                    $container->getParameter(self::PARAM_REMOTE_SCRIPT_REMOVE)
                ),
                new ExecutableFinder(),
                $container->hasParameter(self::PARAM_BOOTSTRAP) ? $container->getParameter(self::PARAM_BOOTSTRAP) : null,
                $container->hasParameter(self::PARAM_PHP_BINARY) ? $container->getParameter(self::PARAM_PHP_BINARY) : null,
                $container->hasParameter(self::PARAM_PHP_CONFIG) ? $container->getParameter(self::PARAM_PHP_CONFIG) : null,
                $container->hasParameter(self::PARAM_PHP_WRAPPER) ? $container->getParameter(self::PARAM_PHP_WRAPPER) : null,
                $container->hasParameter(self::PARAM_PHP_DISABLE_INI) ? $container->getParameter(self::PARAM_PHP_DISABLE_INI) : false
            );
        });

        $container->register(RemoteReflector::class, function (Container $container) {
            return new RemoteReflector($container->get(Launcher::class));
        });

        $container->register(AnnotationReader::class, function (Container $container) {
            return new AnnotationReader($container->getParameter(self::PARAM_ANNOTATION_IMPORT_USE));
        });

        $container->register(AnnotationDriver::class, function (Container $container) {
            return new AnnotationDriver(
                $container->get(RemoteReflector::class),
                $container->getParameter(self::PARAM_SUBJECT_PATTERN),
                $container->get(AnnotationReader::class)
            );
        });

        $container->register(MetadataFactory::class, function (Container $container) {
            return new MetadataFactory(
                $container->get(RemoteReflector::class),
                $container->get(AnnotationDriver::class)
            );
        });

        $container->register(BenchmarkFinder::class, function (Container $container) {
            return new BenchmarkFinder(
                $container->get(MetadataFactory::class)
            );
        });

        $container->register(BaselineManager::class, function (Container $container) {
            $manager = new BaselineManager();
            $callables = array_merge([
                'nothing' => '\PhpBench\Benchmark\Baseline\Baselines::nothing',
                'md5' => '\PhpBench\Benchmark\Baseline\Baselines::md5',
                'file_rw' => '\PhpBench\Benchmark\Baseline\Baselines::fwriteFread',
            ], $container->getParameter(self::PARAM_ENV_BASELINE_CALLABLES));

            foreach ($callables as $name => $callable) {
                $manager->addBaselineCallable($name, $callable);
            }

            return $manager;
        });

        $container->register(TimeUnit::class, function (Container $container) {
            return new TimeUnit(TimeUnit::MICROSECONDS, $container->getParameter(self::PARAM_TIME_UNIT));
        });
    }

    private function registerJson(Container $container): void
    {
        $container->register(JsonDecoder::class, function (Container $container) {
            return new JsonDecoder();
        });
    }

    private function registerCommands(Container $container): void
    {
        $container->register(RunnerHandler::class, function (Container $container) {
            return new RunnerHandler(
                $container->get(Runner::class),
                $container->get(self::SERVICE_REGISTRY_LOGGER),
                $container->get(BenchmarkFinder::class),
                $container->getParameter(self::PARAM_PROGRESS),
                $container->getParameter(self::PARAM_PATH)
            );
        });

        $container->register(ReportHandler::class, function (Container $container) {
            return new ReportHandler(
                $container->get(ReportManager::class)
            );
        });

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

        $container->register(RunCommand::class, function (Container $container) {
            return new RunCommand(
                $container->get(RunnerHandler::class),
                $container->get(ReportHandler::class),
                $container->get(SuiteCollectionHandler::class),
                $container->get(TimeUnitHandler::class),
                $container->get(DumpHandler::class),
                $container->get(self::SERVICE_REGISTRY_DRIVER)
            );
        }, [
            self::TAG_CONSOLE_COMMAND => []
        ]);

        $container->register(ReportCommand::class, function (Container $container) {
            return new ReportCommand(
                $container->get(ReportHandler::class),
                $container->get(TimeUnitHandler::class),
                $container->get(SuiteCollectionHandler::class),
                $container->get(DumpHandler::class)
            );
        }, [
            self::TAG_CONSOLE_COMMAND => []
        ]);

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

    private function registerProgressLoggers(Container $container): void
    {
        $container->register(self::SERVICE_REGISTRY_LOGGER, function (Container $container) {
            $registry = new LoggerRegistry();

            foreach ($container->getServiceIdsForTag(self::TAG_PROGRESS_LOGGER) as $serviceId => $attributes) {
                $registry->addProgressLogger(
                    $attributes['name'],
                    $container->get($serviceId)
                );
            }

            return $registry;
        });

        $container->register(DotsLogger::class, function (Container $container) {
            return new DotsLogger($container->get(OutputInterface::class), $container->get(TimeUnit::class));
        }, [self::TAG_PROGRESS_LOGGER => ['name' => 'dots']]);

        $container->register(DotsLogger::class .'.show', function (Container $container) {
            return new DotsLogger($container->get(OutputInterface::class), $container->get(TimeUnit::class), true);
        }, [self::TAG_PROGRESS_LOGGER => ['name' => 'classdots']]);

        $container->register(VerboseLogger::class, function (Container $container) {
            return new VerboseLogger($container->get(OutputInterface::class), $container->get(TimeUnit::class));
        }, [self::TAG_PROGRESS_LOGGER => ['name' => 'verbose']]);

        $container->register(TravisLogger::class, function (Container $container) {
            return new TravisLogger($container->get(OutputInterface::class), $container->get(TimeUnit::class));
        }, [self::TAG_PROGRESS_LOGGER => ['name' => 'travis']]);

        $container->register(NullLogger::class, function (Container $container) {
            return new NullLogger();
        }, [self::TAG_PROGRESS_LOGGER => ['name' => 'none']]);

        $container->register(BlinkenLogger::class, function (Container $container) {
            return new BlinkenLogger($container->get(OutputInterface::class), $container->get(TimeUnit::class));
        }, [self::TAG_PROGRESS_LOGGER => ['name' => 'blinken']]);

        $container->register(HistogramLogger::class, function (Container $container) {
            return new HistogramLogger($container->get(OutputInterface::class), $container->get(TimeUnit::class));
        }, [self::TAG_PROGRESS_LOGGER => ['name' => 'histogram']]);
    }

    private function registerReportGenerators(Container $container): void
    {
        $container->register(TableGenerator::class, function (Container $container) {
            return new TableGenerator();
        }, [self::TAG_REPORT_GENERATOR => ['name' => 'table']]);
        $container->register(EnvGenerator::class, function (Container $container) {
            return new EnvGenerator();
        }, [self::TAG_REPORT_GENERATOR => ['name' => 'env']]);
        $container->register(CompositeGenerator::class, function (Container $container) {
            return new CompositeGenerator(
                $container->get(ReportManager::class)
            );
        }, [
            self::TAG_REPORT_GENERATOR => ['name' => 'composite']
        ]);
    }

    private function registerReportRenderers(Container $container): void
    {
        $container->register(ConsoleRenderer::class, function (Container $container) {
            return new ConsoleRenderer($container->get(OutputInterface::class), $container->get(Formatter::class));
        }, [self::TAG_REPORT_RENDERER => ['name' => 'console']]);
        $container->register(XsltRenderer::class, function (Container $container) {
            return new XsltRenderer($container->get(OutputInterface::class), $container->get(Formatter::class));
        }, [self::TAG_REPORT_RENDERER => ['name' => 'xslt']]);
        $container->register(DebugRenderer::class, function (Container $container) {
            return new DebugRenderer($container->get(OutputInterface::class));
        }, [self::TAG_REPORT_RENDERER => ['name' => 'debug']]);
        $container->register(DelimitedRenderer::class, function (Container $container) {
            return new DelimitedRenderer($container->get(OutputInterface::class));
        }, [self::TAG_REPORT_RENDERER => ['name' => 'delimited']]);
    }

    private function registerFormatter(Container $container): void
    {
        $container->register(FormatRegistry::class, function (Container $container) {
            $registry = new FormatRegistry();
            $registry->register('printf', new PrintfFormat());
            $registry->register('balance', new BalanceFormat());
            $registry->register('invert_on_throughput', new InvertOnThroughputFormat($container->get(TimeUnit::class)));
            $registry->register('number', new NumberFormat());
            $registry->register('truncate', new TruncateFormat());
            $registry->register('time', new TimeUnitFormat($container->get(TimeUnit::class)));

            return $registry;
        });

        $container->register(Formatter::class, function (Container $container) {
            $formatter = new Formatter($container->get(FormatRegistry::class));
            $formatter->classesFromFile(__DIR__ . '/config/class/main.json');

            return $formatter;
        });
    }

    private function registerAsserters(Container $container): void
    {
        $container->register(AssertionProcessor::class, function () {
            return new AssertionProcessor(
                new ExpressionParser(),
                new ExpressionEvaluatorFactory()
            );
        });
    }

    private function registerRegistries(Container $container): void
    {
        foreach (['generator' => self::PARAM_REPORTS, 'renderer' => self::PARAM_OUTPUTS] as $registryType => $optionName) {
            $container->register('report.registry.' . $registryType, function (Container $container) use ($registryType, $optionName) {
                $registry = new ConfigurableRegistry(
                    $registryType,
                    $container,
                    $container->get(JsonDecoder::class)
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

        $container->register(self::SERVICE_REGISTRY_EXECUTOR, function (Container $container) {
            $registry = new ConfigurableRegistry(
                'executor',
                $container,
                $container->get(JsonDecoder::class)
            );

            foreach ($container->getServiceIdsForTag(self::TAG_EXECUTOR) as $serviceId => $attributes) {
                $registry->registerService($attributes['name'], $serviceId);
            }

            $executorConfigs = array_merge(
                require(__DIR__ . '/config/benchmark/executors.php'),
                $container->getParameter(self::PARAM_EXECUTORS)
            );

            foreach ($executorConfigs as $name => $config) {
                $registry->setConfig($name, $config);
            }

            return $registry;
        });
    }

    public function registerEnvironment(Container $container): void
    {
        $container->register(Provider\Uname::class, function (Container $container) {
            return new Provider\Uname();
        }, [self::TAG_ENV_PROVIDER => []]);

        $container->register(Provider\Php::class, function (Container $container) {
            return new Provider\Php(
                $container->get(Launcher::class)
            );
        }, [self::TAG_ENV_PROVIDER => []]);

        $container->register(Provider\Opcache::class, function (Container $container) {
            return new Provider\Opcache(
                $container->get(Launcher::class)
            );
        }, [self::TAG_ENV_PROVIDER => []]);

        $container->register(Provider\UnixSysload::class, function (Container $container) {
            return new Provider\UnixSysload();
        }, [self::TAG_ENV_PROVIDER => []]);

        $container->register(Provider\Git::class, function (Container $container) {
            return new Provider\Git();
        }, [self::TAG_ENV_PROVIDER => []]);

        $container->register(Provider\Baseline::class, function (Container $container) {
            return new Provider\Baseline(
                $container->get(BaselineManager::class),
                $container->getParameter(self::PARAM_ENV_BASELINES)
            );
        }, [self::TAG_ENV_PROVIDER => []]);

        $container->register(Supplier::class, function (Container $container) {
            $supplier = new Supplier();

            foreach ($container->getServiceIdsForTag(self::TAG_ENV_PROVIDER) as $serviceId => $attributes) {
                $provider = $container->get($serviceId);
                $supplier->addProvider($provider);
            }

            return $supplier;
        });
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
                $container->getParameter(self::PARAM_XML_STORAGE_PATH),
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

    private function relativizeConfigPath(Container $container): void
    {
        $paths = (array)$container->getParameter(self::PARAM_PATH);

        if (empty($paths)) {
            return;
        }

        $container->setParameter(self::PARAM_PATH, array_map(function (string $path) use ($container) {
            if (Path::isAbsolute($path)) {
                return $path;
            }

            return Path::join([
                dirname($container->getParameter(self::PARAM_CONFIG_PATH)),
                $path
            ]);
        }, $paths));
    }
}
