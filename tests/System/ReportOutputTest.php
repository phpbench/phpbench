<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\System;

/**
 * Result set generated with:.
 *
 * ./bin/phpbench run examples/HashBench.php --filter=benchMd5 --report=aggregate --dump-file=tests/System/results/report2.xml --iterations=2
 */
class ReportOutputTest extends SystemTestCase
{
    /**
     * It should generate a HTML report.
     */
    public function testOutputHtml()
    {
        $process = $this->phpbench(
            'report --file=results/report1.xml --report=default --output=\'{"extends": "html", "file": "report.html"}\''
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertGeneratedContents($output, 'html.html');
    }

    /**
     * It should generate a markdown report.
     */
    public function testOutputMarkdown()
    {
        $process = $this->phpbench(
            'report --file=results/report1.xml --report=default --output=\'{"extends": "markdown", "file": "markdown.md"}\''
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertGeneratedContents($output, 'markdown.md');
    }

    /**
     * It should generate a delimited tab output.
     */
    public function testOutputDelimited()
    {
        $process = $this->phpbench(
            'report --file=results/report1.xml --output=\'{"extends": "delimited", "file": "delimited"}\' --report=plain'
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertGeneratedContents($output, 'delimited');
    }

    private function assertGeneratedContents($output, $name)
    {
        $lines = explode("\n", $output);
        array_pop($lines);
        $generatedFilename = array_pop($lines);
        $this->assertFileExists($generatedFilename);
        $this->assertContains(
            file_get_contents(trim(__DIR__ . '/output/' . $name)),
            file_get_contents(trim($generatedFilename))
        );
        unlink($generatedFilename);
    }
}
