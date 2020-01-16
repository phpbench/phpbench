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

use PhpBench\Formatter\Formatter;
use PhpBench\Report\Renderer\ConsoleRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleRendererTest extends AbstractRendererCase
{
    private $renderer;
    private $output;
    private $formatter;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();
        $this->formatter = $this->prophesize(Formatter::class);
    }

    protected function getRenderer()
    {
        $renderer = new ConsoleRenderer($this->formatter->reveal());
        $renderer->setOutput($this->output);

        return $renderer;
    }

    /**
     * It should render a report.
     */
    public function testRender()
    {
        $this->renderReport($this->getReportsDocument(), []);

        $output = $this->output->fetch();
        $this->assertStringContainsString('Report Title', $output);
        $this->assertStringContainsString('Report Description', $output);
        $this->assertStringContainsString('Hello', $output);
        $this->assertStringContainsString('Goodbye', $output);
    }

    /**
     * It should allow the table style to be set.
     */
    public function testTableStyle()
    {
        $this->renderReport($this->getReportsDocument(), [
            'table_style' => 'compact',
        ]);
        $this->addToAssertionCount(1);
    }
}
