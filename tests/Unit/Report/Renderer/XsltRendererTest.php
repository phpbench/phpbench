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

use PhpBench\Report\Renderer\XsltRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

class XsltRendererTest extends AbstractRendererCase
{
    private $renderer;
    private $output;
    private $defaultReport;
    private $specificReport;

    public function setUp()
    {
        $this->renderer = new XsltRenderer();
        $this->output = new BufferedOutput();
        $this->renderer->setOutput($this->output);
        $this->specificReport = 'report_specific.html';

        // this is hard coded in
        $this->defaultReport = getcwd() . '/' . XsltRenderer::DEFAULT_FILENAME;
        if (file_exists($this->defaultReport)) {
            unlink($this->defaultReport);
        }

        if (file_exists($this->specificReport)) {
            unlink($this->specificReport);
        }
    }

    /**
     * It should render an XSLT report.
     */
    public function testRender()
    {
        $reports = $this->getReportsDocument();
        $this->renderer->render($reports, $this->renderer->getDefaultConfig());
        $this->assertFileExists($this->defaultReport);
    }

    /**
     * It should renderer the report using a specific template.
     */
    public function testRenderTemplate()
    {
        $reports = $this->getReportsDocument();
        $this->renderer->render($reports, array_merge(
            $this->renderer->getDefaultConfig(),
            array(
                'template' => __DIR__ . '/templates/test.xsl',
            )
        ));
        $this->assertFileExists($this->defaultReport);
        $this->assertContains('zeeSa8ju', file_get_contents($this->defaultReport));
    }

    /**
     * It should output to a specific file.
     */
    public function testOutputSpecific()
    {
        $reports = $this->getReportsDocument();
        $this->renderer->render($reports, array_merge(
            $this->renderer->getDefaultConfig(),
            array(
                'file' => $this->specificReport,
            )
        ));
        $this->assertFileExists($this->specificReport);
    }

    /**
     * It should throw an exception if the XSLT template does not exist.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage does not exist
     */
    public function testRenderNotExistingTemplate()
    {
        $reports = $this->getReportsDocument();
        $this->renderer->render($reports, array_merge(
            $this->renderer->getDefaultConfig(),
            array('template' => 'not_existing.xsl')
        ));
    }
}
