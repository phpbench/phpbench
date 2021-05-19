<?php

namespace PhpBench\Extension;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Console\Command\Handler\DumpHandler;
use PhpBench\Console\Command\Handler\ReportHandler;
use PhpBench\Console\Command\Handler\SuiteCollectionHandler;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Console\Command\ReportCommand;
use PhpBench\Console\Command\ShowCommand;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Json\JsonDecoder;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Report\Generator\BareGenerator;
use PhpBench\Report\Generator\CompositeGenerator;
use PhpBench\Report\Generator\EnvGenerator;
use PhpBench\Report\Generator\ExpressionGenerator;
use PhpBench\Report\Generator\OutputTestGenerator;
use PhpBench\Report\Renderer\ConsoleRenderer;
use PhpBench\Report\Renderer\DelimitedRenderer;
use PhpBench\Report\Renderer\TemplateRenderer;
use PhpBench\Report\ReportManager;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use PhpBench\Storage\UuidResolver;
use PhpBench\Template\ObjectPathResolver;
use PhpBench\Template\ObjectPathResolver\ChainObjectPathResolver;
use PhpBench\Template\ObjectPathResolver\ReflectionObjectPathResolver;
use PhpBench\Template\ObjectRenderer;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportExtension implements ExtensionInterface
{
    public const PARAM_OUTPUTS = 'report.outputs';
    public const PARAM_REPORTS = 'report.generators';

    public const SERVICE_REGISTRY_GENERATOR = 'report.registry_generator';
    public const SERVICE_REGISTRY_RENDERER = 'report.registry_renderer';

    public const TAG_REPORT_GENERATOR = 'report.generator';
    public const TAG_REPORT_RENDERER = 'report.renderer';
    public const PARAM_TEMPLATE_MAP = 'report.template_map';
    public const PARAM_TEMPLATE_PATHS = 'report.template_paths';

    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_REPORTS => [],
            self::PARAM_OUTPUTS => [],
            self::PARAM_TEMPLATE_PATHS => [
                __DIR__ . '/../../templates'
            ],
            self::PARAM_TEMPLATE_MAP => [
                'PhpBench\\Report\\Model' => 'model'
            ],
        ]);

        $resolver->setAllowedTypes(self::PARAM_TEMPLATE_MAP, ['array']);
        $resolver->setAllowedTypes(self::PARAM_TEMPLATE_PATHS, ['array']);
        $resolver->setAllowedTypes(self::PARAM_REPORTS, ['array']);
        $resolver->setAllowedTypes(self::PARAM_OUTPUTS, ['array']);
        SymfonyOptionsResolverCompat::setInfos($resolver, [
            self::PARAM_REPORTS => 'Report generator configurations, see :doc:`report-generators`',
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
        $this->registerCommands($container);
        $this->registerRegistries($container);
        $this->registerReportGenerators($container);
        $this->registerReportRenderers($container);
    }

    private function registerJson(Container $container): void
    {
        $container->register(JsonDecoder::class, function (Container $container) {
            return new JsonDecoder();
        });
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
        foreach (['generator' => self::PARAM_REPORTS, 'renderer' => self::PARAM_OUTPUTS] as $registryType => $optionName) {
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
        $container->register(ExpressionGenerator::class, function (Container $container) {
            return new ExpressionGenerator(
                $container->get(ExpressionLanguage::class),
                $container->get(Evaluator::class),
                $container->get(EvaluatingPrinter::class),
                new SuiteCollectionTransformer(),
                $container->get(LoggerInterface::class)
            );
        }, [self::TAG_REPORT_GENERATOR => ['name' => 'expression']]);
        $container->register(EnvGenerator::class, function (Container $container) {
            return new EnvGenerator();
        }, [self::TAG_REPORT_GENERATOR => ['name' => 'env']]);
        $container->register(BareGenerator::class, function (Container $container) {
            return new BareGenerator(new SuiteCollectionTransformer());
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
    }

    private function registerReportRenderers(Container $container): void
    {
        $container->register(ConsoleRenderer::class, function (Container $container) {
            return new ConsoleRenderer(
                $container->get(ConsoleExtension::SERVICE_OUTPUT_STD),
                $container->get(Printer::class)
            );
        }, [self::TAG_REPORT_RENDERER => ['name' => 'console']]);

        $container->register(DelimitedRenderer::class, function (Container $container) {
            return new DelimitedRenderer(
                $container->get(ConsoleExtension::SERVICE_OUTPUT_STD),
                $container->get(ExpressionExtension::SERVICE_BARE_PRINTER)
            );
        }, [self::TAG_REPORT_RENDERER => ['name' => 'delimited']]);

        $container->register(TemplateRenderer::class, function (Container $container) {
            return new TemplateRenderer(
                $container->get(ObjectRenderer::class)
            );
        }, [self::TAG_REPORT_RENDERER => ['name' => 'delimited']]);
    }

    private function registerRenderer(Container $container)
    {
        $container->register(ObjectRenderer::class, function (Container $container) {
            return new ObjectRenderer(
                $container->get(ChainObjectPathResolver::class),
                $container->getParameter(self::PARAM_TEMPLATE_PATHS)
            );
        });

        $container->register(ChainObjectPathResolver::class, function (Container $container) {
            return new ChainObjectPathResolver([
                new ReflectionObjectPathResolver(
                    $container->getParameter(self::PARAM_TEMPLATE_MAP)
                )
            ]);
        });
    }
}
