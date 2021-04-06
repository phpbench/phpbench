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
use PhpBench\Expression\Printer;
use PhpBench\Report\Renderer\ConsoleRenderer;
use PhpBench\Report\RendererInterface;
use PhpBench\Tests\Util\Approval;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleRendererTest extends AbstractRendererCase
{
    /**
     * @var BufferedOutput
     */
    private $output;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();
    }

    protected function getRenderer(): RendererInterface
    {
        return new ConsoleRenderer($this->output, $this->container()->get(Printer::class));
    }

    /**
     * @dataProvider provideRender
     */
    public function testRender(string $path): void
    {
        $approval = Approval::create($path, 2);

        $this->renderReport($this->reports(), $approval->getConfig(0));

        $approval->approve($this->output->fetch());
    }

    public function provideRender(): Generator
    {
        foreach (glob(sprintf('%s/%s/*', __DIR__, 'console')) as $path) {
            yield [$path];
        }
    }
}
