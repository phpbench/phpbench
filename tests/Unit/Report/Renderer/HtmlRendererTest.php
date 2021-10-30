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

namespace PhpBench\Tests\Unit\Report\Renderer;

use Generator;
use PhpBench\Registry\Config;
use PhpBench\Report\Renderer\HtmlRenderer;
use PhpBench\Report\RendererInterface;
use PhpBench\Template\ObjectRenderer;
use PhpBench\Tests\Util\Approval;
use Symfony\Component\Console\Output\BufferedOutput;

class HtmlRendererTest extends AbstractRendererCase
{
    /**
     * @var BufferedOutput
     */
    private $output;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->output = new BufferedOutput();
    }

    protected function getRenderer(): RendererInterface
    {
        return new HtmlRenderer(
            $this->output,
            $this->container()->get(ObjectRenderer::class),
            $this->workspace()->path(),
        );
    }

    /**
     * @dataProvider provideRender
     */
    public function testRender(string $path): void
    {
        $approval = Approval::create($path, 2);

        $this->renderReport($this->reports(), $approval->getConfig(0));

        self::assertFileExists($this->workspace()->path('.phpbench/html/index.html'));
        $approval->approve($this->workspace()->getContents('.phpbench/html/index.html'));
    }

    public function provideRender(): Generator
    {
        foreach (glob(sprintf('%s/%s/*', __DIR__, 'template')) as $path) {
            yield [$path];
        }
    }

    public function testCreatesNonExistingDirectory(): void
    {
        $renderer = new HtmlRenderer(
            $this->output,
            $this->container()->get(ObjectRenderer::class),
            $this->workspace()->path('foo/bar')
        );
        $renderer->render($this->reports(), new Config('test', [
            'title' => '',
            'path' => 'foo/bar/index.html'
        ]));
        self::assertFileExists($this->workspace()->path('foo/bar'));
    }
}
