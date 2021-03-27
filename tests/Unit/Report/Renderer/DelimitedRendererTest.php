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

use PhpBench\Expression\Printer;
use PhpBench\Report\Renderer\DelimitedRenderer;
use PhpBench\Report\RendererInterface;
use Symfony\Component\Console\Output\BufferedOutput;

class DelimitedRendererTest extends AbstractRendererCase
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
        return new DelimitedRenderer($this->output, $this->container()->get(Printer::class));
    }

    public function testRender(): void
    {
        $this->renderReport($this->reports(), []);

        $output = $this->output->fetch();
        $this->addToAssertionCount(1);
    }

    public function testRenderComma(): void
    {
        $this->renderReport($this->reports(), [
            'delimiter' => ','
        ]);
        $this->addToAssertionCount(1);
    }
}
