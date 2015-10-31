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

class ReportOutputTest extends SystemTestCase
{
    /**
     * It should generate a HTML report.
     */
    public function testOutputHtml()
    {
        $process = $this->phpbench(
            'report report.xml --report=default --output=html'
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $lines = explode("\n", $output);
        array_pop($lines);
        $generatedFilename = array_pop($lines);
        $this->assertFileExists($generatedFilename);
        $this->assertContains(
            file_get_contents(__DIR__ . '/output/html.html'),
            file_get_contents($generatedFilename)
        );
        unlink($generatedFilename);
    }

    /**
     * It should generate a markdown report.
     */
    public function testOutputMarkdown()
    {
        $process = $this->phpbench(
            'report report.xml --output=markdown --report=default'
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $lines = explode("\n", $output);
        array_pop($lines);
        $generatedFilename = array_pop($lines);
        $this->assertFileExists($generatedFilename);
        $this->assertEquals(
            file_get_contents(trim(__DIR__ . '/output/markdown.md')),
            file_get_contents(trim($generatedFilename))
        );
        unlink($generatedFilename);
    }
}
