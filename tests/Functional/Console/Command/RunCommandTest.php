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

class RunCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should run when given a path.
     */
    public function testCommand()
    {
        $tester = $this->runCommand(array(
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
        $tester = $this->runCommand(array(
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
        $tester = $this->runCommand(array(
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
        $tester = $this->runCommand(array(
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
        $tester = $this->runCommand(array(
            '--report' => array('{"name": "foo_console_ta'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Parameterized bench mark', $display);
    }

    private function runCommand($arguments)
    {
        $arguments = array_merge(array(
            'path' => __DIR__ . '/../../benchmarks',
        ), $arguments);

        $application = new Application();
        $application->add(new RunCommand());
        $command = $application->find('run');
        $tester = new CommandTester($command);
        $tester->execute($arguments);

        return $tester;
    }
}
