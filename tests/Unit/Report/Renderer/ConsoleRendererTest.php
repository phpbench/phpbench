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
use PhpBench\Formatter\Formatter;
use PhpBench\Report\RendererInterface;
use PhpBench\Report\Renderer\ConsoleRenderer;
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
     * It should render a report.
     */
    public function testRender(): void
    {
        $this->renderReport($this->reports(), []);

        $output = $this->output->fetch();
        $this->assertStringContainsString('Output Test Report', $output);
        $this->assertStringContainsString('This report demonstrates', $output);
    }

    /**
     * It should allow the table style to be set.
     */
    public function testTableStyle(): void
    {
        $this->renderReport($this->reports(), [
            'table_style' => 'compact',
        ]);
        $this->addToAssertionCount(1);
    }
}
