<?php

/*
 * This file is part of the PHP Bench package
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
            '--report' => array('full'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Parameterized bench', $display);
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
}
