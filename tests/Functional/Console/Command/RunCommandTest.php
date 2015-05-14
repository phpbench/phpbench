<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Console\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use PhpBench\Console\Command\RunCommand;
use PhpBench\Tests\Functional\Console\Command\BaseCommandTestCase;

class RunCommandTest extends BaseCommandTestCase
{
    /**
     * It should run when given a path.
     */
    public function testCommand()
    {
        $tester = $this->runCommand('run', array(
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Running benchmark suite', $display);
    }

    /**
     * It should run and generate a named report.
     */
    public function testCommandWithReport()
    {
        $tester = $this->runCommand('run', array(
            '--report' => array('console_table'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Parameterized bench mark', $display);
    }

    /**
     * It should run and generate a report configuration.
     */
    public function testCommandWithReportConfiguration()
    {
        $tester = $this->runCommand('run', array(
            '--report' => array('{"name": "console_table"}'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Parameterized bench mark', $display);
    }

    /**
     * It should fail if an unknown report name is given.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown report generator
     */
    public function testCommandWithReportConfigurationUnknown()
    {
        $tester = $this->runCommand('run', array(
            '--report' => array('{"name": "foo_console_table"}'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Parameterized bench mark', $display);
    }

    /**
     * It should fail if an invalid report configuration is given.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not decode JSON string
     */
    public function testCommandWithReportConfigurationInvalid()
    {
        $tester = $this->runCommand('run', array(
            '--report' => array('{"name": "foo_console_ta'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Parameterized bench mark', $display);
    }

    /**
     * It should dump to an XML file
     */
    public function testDumpXml()
    {
        $tester = $this->runCommand('run', array(
            '--dumpfile' => self::TEST_FNAME
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Dumped', $display);
        $this->assertTrue(file_exists(self::TEST_FNAME));
    }

    protected function runCommand($commandName, array $arguments)
    {
        $arguments = array_merge(array(
            'path' => __DIR__ . '/../../benchmarks',
        ), $arguments);

        return parent::runCommand($commandName, $arguments);
    }
}
