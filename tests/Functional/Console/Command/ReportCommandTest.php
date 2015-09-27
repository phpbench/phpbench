<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Console\Command;

class ReportCommandTest extends BaseCommandTestCase
{
    /**
     * It should generate a report.
     */
    public function testGenerateReport()
    {
        $tester = $this->runCommand('report', array(
            'file' => __DIR__ . '/../../report.xml',
            '--report' => array('default'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('benchParameterized', $display);
    }

    /**
     * It should throw an exception if no reports are specified.
     *
     * @expectedException InvalidArgumentException
     */
    public function testNoReports()
    {
        $this->runCommand('report', array(
            'file' => __DIR__ . '/../../report.xml',
        ));
    }

    /**
     * It should throw an exception if the report file does not exist.
     *
     * @expectedException InvalidArgumentException
     */
    public function testNonExistingFile()
    {
        $this->runCommand('report', array(
            'file' => __DIR__ . '/no_exist.xml',
        ));
    }

    /**
     * It should generate a HTML report.
     */
    public function testRendererHtml()
    {
        $tester = $this->runCommand('run', array(
            '--output' => array('html'),
            '--report' => array('default'),
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
        ));

        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $lines = explode("\n", $display);
        array_pop($lines);
        $generatedFilename = array_pop($lines);
        $this->assertFileExists($generatedFilename);
    }
}
