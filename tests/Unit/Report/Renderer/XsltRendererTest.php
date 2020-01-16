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
use PhpBench\Report\Renderer\XsltRenderer;
use RuntimeException;
use Symfony\Component\Console\Output\BufferedOutput;

class XsltRendererTest extends AbstractRendererCase
{
    private $renderer;
    private $output;
    private $defaultReport;
    private $specificReport;
    private $tokenReport;
    private $formatter;

    protected function setUp(): void
    {
        $this->formatter = $this->prophesize(Formatter::class);
        $this->output = new BufferedOutput();
        $this->specificReport = 'report_specific.html';
        $this->tokenReport = 'foobar_test_report.html';

        // this is hard coded in
        $this->defaultReport = getcwd() . '/' . XsltRenderer::DEFAULT_FILENAME;
        $this->clean();
    }

    protected function tearDown(): void
    {
        $this->clean();
    }

    public function clean()
    {
        foreach ([
            $this->defaultReport,
            $this->specificReport,
            $this->tokenReport,
        ] as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    protected function getRenderer()
    {
        $renderer = new XsltRenderer($this->formatter->reveal());
        $renderer->setOutput($this->output);

        return $renderer;
    }

    /**
     * It should render an XSLT report to a file.
     */
    public function testRender()
    {
        $reports = $this->getReportsDocument();
        $this->renderReport($reports, [
            'file' => $this->defaultReport,
        ]);
        $this->assertFileExists($this->defaultReport);
    }

    /**
     * It should renderer the report using a specific template.
     */
    public function testRenderTemplate()
    {
        $reports = $this->getReportsDocument();
        $this->renderReport($reports, [
            'file' => $this->defaultReport,
            'template' => __DIR__ . '/templates/test.xsl',
        ]);
        $this->assertFileExists($this->defaultReport);
        $this->assertStringContainsString('zeeSa8ju', file_get_contents($this->defaultReport));
    }

    /**
     * It should echo to STDOUT if no filename is provided.
     */
    public function testRenderTemplateEmptyFilename()
    {
        $reports = $this->getReportsDocument();
        $this->renderReport($reports, [
            'file' => null,
            'template' => __DIR__ . '/templates/test.xsl',
        ]);
        $output = $this->output->fetch();
        $this->assertStringContainsString('zeeSa8ju', $output);
    }

    /**
     * It should replace the %report_name% token with the report name,.
     */
    public function testRenderTemplateReportNameToken()
    {
        $reports = $this->getReportsDocument();
        $this->renderReport($reports, [
            'template' => __DIR__ . '/templates/test.xsl',
            'file' => 'foobar_%report_name%.html',
        ]);
        $this->assertFileExists($this->tokenReport);
        $this->assertStringContainsString('zeeSa8ju', file_get_contents($this->tokenReport));
    }

    /**
     * It should output to a specific file.
     */
    public function testOutputSpecific()
    {
        $reports = $this->getReportsDocument();
        $this->renderReport($reports, [
            'file' => $this->specificReport,
        ]);
        $this->assertFileExists($this->specificReport);
    }

    /**
     * It should throw an exception if the XSLT template does not exist.
     *
     */
    public function testRenderNotExistingTemplate()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not exist');
        $reports = $this->getReportsDocument();
        $this->renderReport($reports, [
            'file' => $this->defaultReport,
            'template' => 'not_existing.xsl',
        ]);
    }
}
