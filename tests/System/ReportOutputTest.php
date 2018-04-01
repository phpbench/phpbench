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

namespace PhpBench\Tests\System;

/**
 * Result set generated with:.
 *
 * ./bin/phpbench run examples/HashBench.php --filter=benchMd5 --report=aggregate --dump-file=tests/System/results/report1.xml --iterations=1
 */
class ReportOutputTest extends SystemTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->getResult();
    }

    /**
     * It should generate a HTML report.
     */
    public function testOutputHtml()
    {
        $process = $this->phpbench(
            'report --file=' . $this->fname . ' --report=default --output=\'{"extends": "html", "file": "report.html"}\''
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
            'report --file=' . $this->fname . ' --report=default --output=\'{"extends": "markdown", "file": "markdown.md"}\''
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertGeneratedContents($output, 'markdown.md');
    }

    /**
     * It should generate a delimited tab output.
     */

    /**
     * @dataProvider provideOutputDelimited
     */
    public function testOutputDelimited($reportName)
    {
        $process = $this->phpbench(
            'report --file=' . $this->fname . ' --report=default --output=\'{"extends": "' . $reportName . '", "file": "delimited", "header": false}\''
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertGeneratedContents($output, $reportName);
    }

    public function provideOutputDelimited()
    {
        return [
            [
                'delimited',
            ],
            [
                'csv',
            ],
        ];
    }

    private function assertGeneratedContents($output, $name)
    {
        $lines = explode("\n", $output);

        array_pop($lines);
        $generatedFilename = array_pop($lines);
        $this->assertFileExists($generatedFilename);

        $expected = file_get_contents(trim(__DIR__ . '/output/' . $name));
        $actual = file_get_contents(trim($generatedFilename));

        // replace the unique suite hash with %run.uuid%
        $actual = preg_replace('{([0-9a-f]{40})}', '%run.uuid%', $actual);
        $actual = preg_replace('{([0-9]{4}-[0-9]{2}-[0-9]{2})}', '%date%', $actual);
        $actual = preg_replace('{([0-9]{2}:[0-9]{2}:[0-9]{2})}', '%time%', $actual);

        $this->assertContains($expected, $actual);
        unlink($generatedFilename);
    }
}
