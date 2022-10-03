<?php

namespace PhpBench\Extension;

use PhpBench\Color\GradientBuilder;
use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Console\Command\Handler\DumpHandler;
use PhpBench\Console\Command\Handler\ReportHandler;
use PhpBench\Console\Command\Handler\SuiteCollectionHandler;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Console\Command\ReportCommand;
use PhpBench\Console\Command\ShowCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Expression\NodePrinters;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Printer\NormalizingPrinter;
use PhpBench\Json\JsonDecoder;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Report\Bridge\ExpressionBridge;
use PhpBench\Report\ComponentGenerator\BarChartAggregateComponentGenerator;
use PhpBench\Report\ComponentGenerator\TableAggregateComponent;
use PhpBench\Report\ComponentGenerator\TableAggregate\ExpandColumnProcessor;
use PhpBench\Report\ComponentGenerator\TableAggregate\ExpressionColumnProcessor;
use PhpBench\Report\ComponentGenerator\TextComponentGenerator;
use PhpBench\Report\Console\ObjectRenderer as ConsoleObjectRenderer;
use PhpBench\Report\Console\Renderer\BarChartRenderer;
use PhpBench\Report\Console\Renderer\ReportRenderer;
use PhpBench\Report\Console\Renderer\ReportsRenderer;
use PhpBench\Report\Console\Renderer\TableRenderer;
use PhpBench\Report\Console\Renderer\TextRenderer;
use PhpBench\Report\Generator\BareGenerator;
use PhpBench\Report\Generator\ComponentGenerator;
use PhpBench\Report\Generator\CompositeGenerator;
use PhpBench\Report\Generator\EnvGenerator;
use PhpBench\Report\Generator\ExpressionGenerator;
use PhpBench\Report\Generator\OutputTestGenerator;
use PhpBench\Report\Renderer\ConsoleRenderer;
use PhpBench\Report\Renderer\DelimitedRenderer;
use PhpBench\Report\Renderer\HtmlRenderer;
use PhpBench\Report\ReportManager;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use PhpBench\Storage\UuidResolver;
use PhpBench\Template\Expression\Printer\TemplateNodePrinter;
use PhpBench\Template\Expression\Printer\TemplatePrinter;
use PhpBench\Template\ObjectPathResolver\ChainObjectPathResolver;
use PhpBench\Template\ObjectPathResolver\ReflectionObjectPathResolver;
use PhpBench\Template\ObjectRenderer;
use PhpBench\Template\TemplateService\ContainerTemplateService;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportExtension implements ExtensionInterface
{
    public const PARAM_OUTPUTS = 'report.outputs';
    public const PARAM_REPORTS = 'report.generators';
    public const PARAM_COMPONENTS = 'report.components';

    public const SERVICE_REGISTRY_GENERATOR = 'report.registry_generator';
    public const SERVICE_REGISTRY_RENDERER = 'report.registry_renderer';
    public const SERVICE_REGISTRY_COMPONENT = 'report.registry_component';

    public const TAG_REPORT_GENERATOR = 'report.generator';
    public const TAG_COMPONENT_GENERATOR = 'report.component';
    public const TAG_REPORT_RENDERER = 'report.renderer';
    public const PARAM_TEMPLATE_MAP = 'report.template_map';
    public const PARAM_TEMPLATE_PATHS = 'report.template_paths';

    /**
     * This configuration has been removed as it did not do anything. If this
     * setting is given it will be ignored, and in 2.0 an exception will be
     * raised used.
     */
    public const PARAM_OUTPUT_DIR_HTML = 'report.html_output_dir';

    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_REPORTS => [],
            self::PARAM_OUTPUTS => [],
            self::PARAM_COMPONENTS => [],
            self::PARAM_TEMPLATE_PATHS => [
                __DIR__ . '/../../templates'
            ],
            self::PARAM_TEMPLATE_MAP => [
                'PhpBench\\Report\\Model' => 'html',
                'PhpBench\\Expression\\Ast' => 'html/node'
            ],
        ]);

        $resolver->setAllowedTypes(self::PARAM_TEMPLATE_MAP, ['array']);
        $resolver->setAllowedTypes(self::PARAM_TEMPLATE_PATHS, ['array']);
        $resolver->setAllowedTypes(self::PARAM_REPORTS, ['array']);
        $resolver->setAllowedTypes(self::PARAM_OUTPUTS, ['array']);
        $resolver->setAllowedTypes(self::PARAM_COMPONENTS, ['array']);
        SymfonyOptionsResolverCompat::setInfos($resolver, [
            self::PARAM_REPORTS => 'Report generator configurations, see :doc:`report-generators`',
            self::PARAM_COMPONENTS => 'Component configurations, see :doc:`report-components`',
            self::PARAM_OUTPUTS => 'Report renderer configurations, see :doc:`report-renderers`',
            self::PARAM_TEMPLATE_MAP => 'Namespace prefix to template path map for object rendering',
            self::PARAM_TEMPLATE_PATHS => 'List of paths to load templates from',
        ]);
    }

    public function load(Container $container): void
    {
        $container->register(ReportManager::class, function (Container $container) {
            return new ReportManager(
                $container->get(self::SERVICE_REGISTRY_GENERATOR),
                $container->get(self::SERVICE_REGISTRY_RENDERER)
            );
        });

        $this->registerRenderer($container);
        $this->registerConsoleRenderer($container);
        $this->registerCommands($container);
        $this->registerRegistries($container);
        $this->registerReportGenerators($container);
        $this->registerReportRenderers($container);
        $this->registerComponentGenerators($container);
        $this->registerBridge($container);
    }

    private function registerCommands(Container $container): void
    {
        $container->register(ReportHandler::class, function (Container $container) {
            return new ReportHandler(
                $container->get(ReportManager::class)
            );
        });

        $container->register(ReportCommand::class, function (Container $container) {
            return new ReportCommand(
                $container->get(ReportHandler::class),
                $container->get(TimeUnitHandler::class),
                $container->get(SuiteCollectionHandler::class),
                $container->get(DumpHandler::class)
            );
        }, [
            ConsoleExtension::TAG_CONSOLE_COMMAND => []
        ]);

        $container->register(ShowCommand::class, function (Container $container) {
            return new ShowCommand(
                $container->get(StorageExtension::SERVICE_REGISTRY_DRIVER),
                $container->get(ReportHandler::class),
                $container->get(TimeUnitHandler::class),
                $container->get(DumpHandler::class),
                $container->get(UuidResolver::class)
            );
        }, [
            ConsoleExtension::TAG_CONSOLE_COMMAND => []
        ]);
    }

    private function registerRegistries(Container $container): void
    {
        foreach ([
            'generator' => self::PARAM_REPORTS,
            'renderer' => self::PARAM_OUTPUTS,
            'component' => self::PARAM_COMPONENTS,
        ] as $registryType => $optionName) {
            $container->register('report.registry_' . $registryType, function (Container $container) use ($registryType, $optionName) {
                $registry = new ConfigurableRegistry(
                    $registryType,
                    $container,
                    $container->get(JsonDecoder::class)
                );

                foreach ($container->getServiceIdsForTag('report.' . $registryType) as $serviceId => $attributes) {
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
    }

    private function registerReportGenerators(Container $container): void
    {
        $container->register(SuiteCollectionTransformer::class, function (Container $container) {
            return new SuiteCollectionTransformer();
        });
        $container->register(ExpressionGenerator::class, function (Container $container) {
            return new ExpressionGenerator(
                $container->get(ExpressionEvaluator::class),
                $container->get(SuiteCollectionTransformer::class),
                $container->get(LoggerInterface::class)
            );
        }, [self::TAG_REPORT_GENERATOR => ['name' => 'expression']]);
        $container->register(EnvGenerator::class, function (Container $container) {
            return new EnvGenerator();
        }, [self::TAG_REPORT_GENERATOR => ['name' => 'env']]);
        $container->register(BareGenerator::class, function (Container $container) {
            return new BareGenerator($container->get(SuiteCollectionTransformer::class));
        }, [self::TAG_REPORT_GENERATOR => ['name' => 'bare']]);
        $container->register(OutputTestGenerator::class, function (Container $container) {
            return new OutputTestGenerator();
        }, [self::TAG_REPORT_GENERATOR => ['name' => 'output_test']]);
        $container->register(CompositeGenerator::class, function (Container $container) {
            return new CompositeGenerator(
                $container->get(ReportManager::class)
            );
        }, [
            self::TAG_REPORT_GENERATOR => ['name' => 'composite']
        ]);
        $container->register(ComponentGenerator::class, function (Container $container) {
            return new ComponentGenerator(
                $container->get(SuiteCollectionTransformer::class),
                $container->get(self::SERVICE_REGISTRY_COMPONENT),
                $container->get(ExpressionBridge::class),
                $container->get(LoggerInterface::class)
            );
        }, [
            self::TAG_REPORT_GENERATOR => ['name' => 'component'],
            self::TAG_COMPONENT_GENERATOR => ['name' => 'section']
        ]);
    }

    private function registerReportRenderers(Container $container): void
    {
        $container->register(ConsoleRenderer::class, function (Container $container) {
            return new ConsoleRenderer(
                $container->get(ConsoleObjectRenderer::class)
            );
        }, [self::TAG_REPORT_RENDERER => ['name' => 'console']]);

        $container->register(DelimitedRenderer::class, function (Container $container) {
            return new DelimitedRenderer(
                $container->get(ConsoleExtension::SERVICE_OUTPUT_STD),
                $container->get(ExpressionExtension::SERVICE_BARE_PRINTER)
            );
        }, [self::TAG_REPORT_RENDERER => ['name' => 'delimited']]);

        $container->register(HtmlRenderer::class, function (Container $container) {
            return new HtmlRenderer(
                $container->get(ConsoleExtension::SERVICE_OUTPUT_STD),
                $container->get(ObjectRenderer::class),
                $container->getParameter(CoreExtension::PARAM_WORKING_DIR)
            );
        }, [self::TAG_REPORT_RENDERER => ['name' => 'html']]);
    }

    private function registerRenderer(Container $container): void
    {
        $container->register(ObjectRenderer::class, function (Container $container) {
            return new ObjectRenderer(
                $container->get(ChainObjectPathResolver::class),
                $container->getParameter(self::PARAM_TEMPLATE_PATHS),
                new ContainerTemplateService($container, [
                    'nodeEvaluator' => ExpressionEvaluator::class,
                    'nodePrinter' => TemplatePrinter::class,
                    'gradientBuilder' => GradientBuilder::class
                ])
            );
        });

        $container->register(GradientBuilder::class, function (Container $container) {
            return new GradientBuilder();
        });

        $container->register(ChainObjectPathResolver::class, function (Container $container) {
            return new ChainObjectPathResolver([
                new ReflectionObjectPathResolver(
                    $container->getParameter(self::PARAM_TEMPLATE_MAP)
                )
            ]);
        });

        $container->register(TemplatePrinter::class, function (Container $container) {
            return new TemplatePrinter(
                new NormalizingPrinter(
                    new TemplateNodePrinter(
                        $container->get(ObjectRenderer::class),
                        $container->get(NodePrinters::class)
                    )
                )
            );
        });
    }

    private function registerComponentGenerators(Container $container): void
    {
        $container->register(TableAggregateComponent::class, function (Container $container) {
            return new TableAggregateComponent(
                $container->get(ExpressionBridge::class),
                [
                    'expression' => $container->get(ExpressionColumnProcessor::class),
                    'expand' => $container->get(ExpandColumnProcessor::class),
                ]
            );
        }, [
            self::TAG_COMPONENT_GENERATOR => [ 'name' => 'table_aggregate' ]
        ]);

        $container->register(ExpressionColumnProcessor::class, function (Container $container) {
            return new ExpressionColumnProcessor($container->get(ExpressionBridge::class));
        });
        $container->register(ExpandColumnProcessor::class, function (Container $container) {
            return new ExpandColumnProcessor($container->get(ExpressionBridge::class));
        });

        $container->register(BarChartAggregateComponentGenerator::class, function (Container $container) {
            return new BarChartAggregateComponentGenerator($container->get(ExpressionEvaluator::class));
        }, [
            self::TAG_COMPONENT_GENERATOR => [ 'name' => 'bar_chart_aggregate' ]
        ]);
        $container->register(TextComponentGenerator::class, function (Container $container) {
            return new TextComponentGenerator($container->get(ExpressionEvaluator::class));
        }, [
            self::TAG_COMPONENT_GENERATOR => [ 'name' => 'text' ]
        ]);
    }

    private function registerConsoleRenderer(Container $container): void
    {
        $container->register(ConsoleObjectRenderer::class, function (Container $container) {
            return new ConsoleObjectRenderer(
                $container->get(ConsoleExtension::SERVICE_OUTPUT_STD),
                new ReportsRenderer(),
                new BarChartRenderer(
                    $container->get(ExpressionEvaluator::class),
                    $container->get(ExpressionExtension::SERVICE_PLAIN_PRINTER)
                ),
                new ReportRenderer(),
                new TableRenderer($container->get(Printer::class)),
                new TextRenderer()
            );
        }, [
        ]);
    }

    private function registerBridge(Container $container): void
    {
        $container->register(ExpressionBridge::class, function (Container $container) {
            return new ExpressionBridge(
                $container->get(ExpressionEvaluator::class)
            );
        });
    }
}
