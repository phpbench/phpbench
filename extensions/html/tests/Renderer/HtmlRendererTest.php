<?php

namespace PhpBench\Extensions\Html\Tests\Renderer;

use Generator;
use PhpBench\DependencyInjection\Container;
use PhpBench\Expression\Printer;
use PhpBench\Extension\CoreExtension;
use PhpBench\Extension\ExpressionExtension;
use PhpBench\Extensions\Html\HtmlExtension;
use PhpBench\Extensions\Html\Renderer\HtmlRenderer;
use PhpBench\Extensions\Html\ObjectRenderers;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\OutputTestGenerator;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Renderer\ConsoleRenderer;
use PhpBench\Report\RendererInterface;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\TestCase;
use PhpBench\Tests\Unit\Report\Renderer\AbstractRendererCase;
use PhpBench\Tests\Util\Approval;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Tests\Util\Workspace;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function file_get_contents;

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
        dump($compare);
    }

    private function render(Reports $reports, array $config)
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
