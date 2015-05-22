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
            'path' => __DIR__ . '/../../benchmarks',
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
            'path' => __DIR__ . '/../../benchmarks',
            '--report' => array('console_table'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Parameterized bench mark', $display);
    }

    /**
     * It should throw an exception if no path is given (and no path is configured)
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You must
     */
    public function testCommandWithNoPath()
    {
        $this->runCommand('run', array(
            '--report' => array('console_table'),
        ));
    }

    /**
     * It should run and generate a report configuration.
     */
    public function testCommandWithReportConfiguration()
    {
        $tester = $this->runCommand('run', array(
            'path' => __DIR__ . '/../../benchmarks',
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
            'path' => __DIR__ . '/../../benchmarks',
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
            'path' => __DIR__ . '/../../benchmarks',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Parameterized bench mark', $display);
    }

    /**
     * It should dump to an XML file.
     */
    public function testDumpXml()
    {
        $tester = $this->runCommand('run', array(
            '--dumpfile' => self::TEST_FNAME,
            'path' => __DIR__ . '/../../benchmarks',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Dumped', $display);
        $this->assertTrue(file_exists(self::TEST_FNAME));
    }

    /**
     * It should dump to stdout
     */
    public function testDumpXmlStdOut()
    {
        $tester = $this->runCommand('run', array(
            '--dump' => true,
            'path' => __DIR__ . '/../../benchmarks',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $dom = new \DOMDocument();
        $dom->loadXml($display);
        $xpath = new \DOMXPath($dom);
        $benchmarkEls = $xpath->query('//subject');
        $this->assertEquals(3, $benchmarkEls->length);
    }

    /**
     * It should accept explicit parameters
     */
    public function testExplicitParameters()
    {
        $tester = $this->runCommand('run', array(
            '--dump' => true,
            '--parameters' => '{"length": 333}',
            'path' => __DIR__ . '/../../benchmarks',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $dom = new \DOMDocument();
        $dom->loadXml($display);
        $xpath = new \DOMXPath($dom);
        $parameters = $xpath->query('//parameter[@value=333]');
        $this->assertEquals(3, $parameters->length);
    }

    /**
     * It should throw an exception if an invalid JSON string is provided for parameters
     *
     * @expectedException InvalidArgumentException
     */
    public function testExplicitParametersInvalidJson()
    {
        $this->runCommand('run', array(
            '--dump' => true,
            '--parameters' => '{"length: 3,',
            'path' => __DIR__ . '/../../benchmarks',
        ));
    }
}
