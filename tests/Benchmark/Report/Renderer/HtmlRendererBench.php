<?php

namespace PhpBench\Tests\Benchmark\Report\Renderer;

use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\OutputTestGenerator;
use PhpBench\Report\Renderer\HtmlRenderer;
use PhpBench\Tests\Benchmark\IntegrationBenchCase;

final class HtmlRendererBench extends IntegrationBenchCase
{
    /**
     * @var OutputTestGenerator
     */
    private $reports;
    /**
     * @var HtmlRenderer
     */
    private $renderer;

    public function __construct()
    {
        $this->reports = (new OutputTestGenerator())->generate(new SuiteCollection([]), new Config('a', []));
        $this->renderer = $this->container()->get(HtmlRenderer::class);
    }

    public function benchRender(): void
    {
        $this->renderer->render($this->reports, new Config('a', [
            'title' => '',
            'path' => '.phpbench/html/bench_test.html'
        ]));
    }
}
