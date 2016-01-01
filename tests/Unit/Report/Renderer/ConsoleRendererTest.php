<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report\Renderer;

use PhpBench\Registry\Config;
use PhpBench\Report\Renderer\ConsoleRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleRendererTest extends AbstractRendererCase
{
    private $renderer;
    private $output;

    public function setUp()
    {
        $this->renderer = new ConsoleRenderer();
        $this->output = new BufferedOutput();
        $this->renderer->setOutput($this->output);
    }

    /**
     * It should render a report.
     */
    public function testRender()
    {
        $this->renderer->render($this->getReportsDocument(), new Config($this->renderer->getDefaultConfig()));

        $output = $this->output->fetch();
        $this->assertContains('Report Title', $output);
        $this->assertContains('Report Description', $output);
        $this->assertContains('Hello', $output);
        $this->assertContains('Goodbye', $output);
    }

    /**
     * It should allow the table style to be set.
     */
    public function testTableStyle()
    {
        $this->renderer->render($this->getReportsDocument(), new Config(array_merge(
            $this->renderer->getDefaultConfig(),
            array(
                'table_style' => 'compact',
            )
        )));
    }
}
