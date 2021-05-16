<?php

namespace PhpBench\Extensions\Html\Tests\Renderer;

use function file_get_contents;
use Generator;
use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Extensions\Html\HtmlExtension;
use PhpBench\Extensions\Html\Report\Renderer\HtmlRenderer;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\OutputTestGenerator;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\RendererInterface;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\Approval;
use PhpBench\Tests\Util\TestUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlRendererTest extends IntegrationTestCase
{
    protected function getRenderer(): RendererInterface
    {
        $container = new Container([
            HtmlExtension::class,
            ExpressionExtension::class,
            CoreExtension::class
        ], []);
        $container->init();

        return $container->get(HtmlRenderer::class);
    }

    /**
     * @dataProvider provideRender
     */
    public function testRender(string $path): void
    {
        $approval = Approval::create($path, 2);
        $config = $approval->getConfig(0);
        $config[HtmlRenderer::PARAM_OUTPUT_DIR] = $this->workspace()->path('html');
        $collection = TestUtil::createCollection([]);
        $reports = (new OutputTestGenerator())->generate($collection, new Config('foo', []));

        $this->render($reports, $config);

        $compare = [];

        foreach (glob($this->workspace()->path('html') . '/*.html') as $path) {
            $compare[] = '// ' . $path;
            $compare[] = file_get_contents($path);
        }

        $approval->approve(implode("\n", $compare));
    }

    private function render(Reports $reports, array $config): void
    {
        $renderer = $this->getRenderer();
        $options = new OptionsResolver();
        $renderer->configure($options);
        $renderer->render($reports, new Config('asd', $options->resolve($config)));
    }

    public function provideRender(): Generator
    {
        foreach (glob(sprintf('%s/%s/*', __DIR__, 'approval')) as $path) {
            yield [$path];
        }
    }
}
