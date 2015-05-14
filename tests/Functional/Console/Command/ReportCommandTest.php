<?php

namespace PhpBench\Tests\Functional\Console\Command;

use PhpBench\Tests\Functional\Console\Command\BaseCommandTestCase;

class ReportCommandTest extends BaseCommandTestCase
{
    /**
     * It should generate a report
     */
    public function testGenerateReport()
    {
        $tester = $this->runCommand('report', array(
            'file' => __DIR__ . '/report_command.xml',
            '--report' => array('console_table'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Parameterized bench', $display);
    }

    /**
     * It should throw an exception if no reports are specified
     *
     * @expectedException InvalidArgumentException
     */
    public function testNoReports()
    {
        $this->runCommand('report', array(
            'file' => __DIR__ . '/report_command.xml',
        ));
    }

    /**
     * It should throw an exception if the report file does not exist
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
