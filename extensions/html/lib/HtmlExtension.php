<?php

namespace PhpBench\Extensions\Html;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\NodePrinters;
use PhpBench\Expression\Printer;
use PhpBench\Expression\Printer\NormalizingPrinter;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Extension\ReportExtension;
use PhpBench\Extensions\Html\Expression\NodePrinter\HtmlHighlightingNodePrinter;
use PhpBench\Extensions\Html\Report\Renderer\HtmlRenderer;
use PhpBench\Extensions\Html\Template\HtmlLayoutRenderer;
use PhpBench\Extensions\Html\Template\NodeRenderer;
use PhpBench\Extensions\Html\Template\ReportRenderer;
use PhpBench\Extensions\Html\Template\ReportsRenderer;
use PhpBench\Extensions\Html\Template\TableRenderer;
use PhpBench\Extensions\Html\Template\TemplateRenderer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlExtension implements ExtensionInterface
{
    const TAG_TEMPLATE_RENDERER = 'html.renderer';

    /**
     * {@inheritDoc}
     */
    public function load(Container $container): void
    {
        $container->register(HtmlRenderer::class, function (Container $container) {
            return new HtmlRenderer(
                $container->get(ObjectRenderers::class)
            );
        }, [
            ReportExtension::TAG_REPORT_RENDERER => [
                'name' => 'html',
            ],
        ]);

        $this->registerRenderers($container);
    }

    public function registerRenderers(Container $container): void
    {
        $container->register(ObjectRenderers::class, function (Container $container) {
            return new ObjectRenderers(array_map(function (string $serviceId) use ($container) {
                return $container->get($serviceId);

            }, array_keys($container->getServiceIdsForTag(self::TAG_TEMPLATE_RENDERER))));
        });

        $container->register(HtmlLayoutRenderer::class, function (Container $container) {
            return new HtmlLayoutRenderer();
        }, [
            self::TAG_TEMPLATE_RENDERER => []
        ]);

        $container->register(ReportsRenderer::class, function (Container $container) {
            return new ReportsRenderer();
        }, [
            self::TAG_TEMPLATE_RENDERER => []
        ]);

        $container->register(TableRenderer::class, function (Container $container) {
            return new TableRenderer();
        }, [
            self::TAG_TEMPLATE_RENDERER => []
        ]);
        $container->register(ReportRenderer::class, function (Container $container) {
            return new ReportRenderer();
        }, [
            self::TAG_TEMPLATE_RENDERER => []
        ]);

        $container->register(NodeRenderer::class, function (Container $container) {
            return new NodeRenderer(new NormalizingPrinter(
                new HtmlHighlightingNodePrinter($container->get(NodePrinters::class))
            ));
        }, [
            self::TAG_TEMPLATE_RENDERER => []
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $resolver): void
    {
    }
}
