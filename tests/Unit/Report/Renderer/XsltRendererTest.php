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
use PhpBench\Report\Renderer\XsltRenderer;
use Symfony\Component\Console\Output\BufferedOutput;

class XsltRendererTest extends AbstractRendererCase
{
    private $renderer;
    private $output;
    private $defaultReport;
    private $specificReport;
    private $tokenReport;

    public function setUp()
    {
        $this->renderer = new XsltRenderer();
        $this->output = new BufferedOutput();
        $this->renderer->setOutput($this->output);
        $this->specificReport = 'report_specific.html';
        $this->tokenReport = 'foobar_test_report.html';

        // this is hard coded in
        $this->defaultReport = getcwd() . '/' . XsltRenderer::DEFAULT_FILENAME;
        $this->clean();
    }

    public function tearDown()
    {
        $this->clean();
    }

    public function clean()
    {
        foreach (array(
            $this->defaultReport,
            $this->specificReport,
            $this->tokenReport,
        ) as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    /**
     * It should render an XSLT report to a file.
     */
    public function testRender()
    {
        $reports = $this->getReportsDocument();
        $this->renderer->render($reports, new Config('test', array_merge(
            $this->renderer->getDefaultConfig(),
            array(
                'file' => $this->defaultReport,
            )
        )));
        $this->assertFileExists($this->defaultReport);
    }

    /**
     * It should renderer the report using a specific template.
     */
    public function testRenderTemplate()
    {
        $reports = $this->getReportsDocument();
        $this->renderer->render($reports, new Config('test', array_merge(
            $this->renderer->getDefaultConfig(),
            array(
                'template' => __DIR__ . '/templates/test.xsl',
                'file' => $this->defaultReport,
            )
        )));
        $this->assertFileExists($this->defaultReport);
        $this->assertContains('zeeSa8ju', file_get_contents($this->defaultReport));
    }

    /**
     * It should echo to STDOUT if no filename is provided.
     */
    public function testRenderTemplateEmptyFilename()
    {
        $reports = $this->getReportsDocument();
        $this->renderer->render($reports, new Config('test', array_merge(
            $this->renderer->getDefaultConfig(),
            array(
                'template' => __DIR__ . '/templates/test.xsl',
                'file' => null,
            )
        )));
        $output = $this->output->fetch();
        $this->assertContains('zeeSa8ju', $output);
    }

    /**
     * It should replace the %report_name% token with the report name,.
     */
    public function testRenderTemplateReportNameToken()
    {
        $reports = $this->getReportsDocument();
        $this->renderer->render($reports, new Config('test', array_merge(
            $this->renderer->getDefaultConfig(),
            array(
                'template' => __DIR__ . '/templates/test.xsl',
                'file' => 'foobar_%report_name%.html',
            )
        )));
        $this->assertFileExists($this->tokenReport);
        $this->assertContains('zeeSa8ju', file_get_contents($this->tokenReport));
    }

    /**
     * It should output to a specific file.
     */
    public function testOutputSpecific()
    {
        $reports = $this->getReportsDocument();
        $this->renderer->render($reports, new Config('test', array_merge(
            $this->renderer->getDefaultConfig(),
            array(
                'file' => $this->specificReport,
            )
        )));
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
        $this->renderer->render($reports, new Config('test', array_merge(
            $this->renderer->getDefaultConfig(),
            array('template' => 'not_existing.xsl')
        )));
    }
}
