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
    private $pidPath;

    public function setUp()
    {
        $this->pidPath = sys_get_temp_dir() . '/phpbench_isolationtest';
        if (file_exists($this->pidPath)) {
            unlink($this->pidPath);
        }
    }

    public function tearDown()
    {
        $this->setUp();
    }

    /**
     * It should run when given a path.
     */
    public function testCommand()
    {
        $tester = $this->runCommand('run', array(
            'path' => __DIR__ . '/../../benchmarks/BenchmarkCase.php',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Running benchmarks', $display);
    }

    /**
     * It should run and generate a named report.
     */
    public function testCommandWithReport()
    {
        $tester = $this->runCommand('run', array(
            'path' => __DIR__ . '/../../benchmarks/BenchmarkCase.php',
            '--report' => array('console_table'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Parameterized bench mark', $display);
    }

    /**
     * It should throw an exception if no path is given (and no path is configured).
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
            'path' => __DIR__ . '/../../benchmarks/BenchmarkCase.php',
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
            'path' => __DIR__ . '/../../benchmarks/BenchmarkCase.php',
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
            'path' => __DIR__ . '/../../benchmarks/BenchmarkCase.php',
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
            'path' => __DIR__ . '/../../benchmarks/BenchmarkCase.php',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Dumped', $display);
        $this->assertTrue(file_exists(self::TEST_FNAME));
    }

    /**
     * It should dump to stdout.
     */
    public function testDumpXmlStdOut()
    {
        $tester = $this->runCommand('run', array(
            '--dump' => true,
            'path' => __DIR__ . '/../../benchmarks/BenchmarkCase.php',
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
     * It should accept explicit parameters.
     */
    public function testOverrideParameters()
    {
        $tester = $this->runCommand('run', array(
            '--dump' => true,
            '--parameters' => '{"length": 333}',
            'path' => __DIR__ . '/../../benchmarks/BenchmarkCase.php',
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
     * It should throw an exception if an invalid JSON string is provided for parameters.
     *
     * @expectedException InvalidArgumentException
     */
    public function testOverrideParametersInvalidJson()
    {
        $this->runCommand('run', array(
            '--dump' => true,
            '--parameters' => '{"length: 3,',
            'path' => __DIR__ . '/../../benchmarks/BenchmarkCase.php',
        ));
    }

    /**
     * Its should allow the number of iterations to be specified.
     */
    public function testOverrideIterations()
    {
        $tester = $this->runCommand('run', array(
            '--subject' => 'benchRandom',
            '--dump' => true,
            '--iterations' => 10,
            'path' => __DIR__ . '/../../benchmarks/BenchmarkCase.php',
        ));

        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $dom = new \DOMDocument();
        $dom->loadXml($display);
        $dom->formatOutput = true;
        $xpath = new \DOMXPath($dom);
        $parameters = $xpath->query('//iteration');
        $this->assertEquals(10, $parameters->length);
    }

    /**
     * It can run each iteration in isolation.
     * There are 2 subjects each with 5 iterations, so there should be 10 PIDs.
     */
    public function testProcessIsolationIteration()
    {
        $this->runCommand('run', array(
            '--processisolation' => 'iteration',
            'path' => __DIR__ . '/../../benchmarks/IsolatedCase.php',
        ));

        $pids = array_unique(explode(PHP_EOL, trim(file_get_contents($this->pidPath))));
        $this->assertCount(10, $pids);
    }

    /**
     * It can run each set of iterations in isolation.
     * There are 2 subjects, so there should be 2 PIDs.
     */
    public function testProcessIsolationIterations()
    {
        $this->runCommand('run', array(
            '--processisolation' => 'iterations',
            'path' => __DIR__ . '/../../benchmarks/IsolatedCase.php',
        ));

        $pids = array_unique(explode(PHP_EOL, trim(file_get_contents($this->pidPath))));
        $this->assertCount(2, $pids);
    }

    /**
     * It can have the progress logger specified.
     */
    public function testProgressLogger()
    {
        $tester = $this->runCommand('run', array(
            '--progress' => 'benchdots',
            'path' => __DIR__ . '/../../benchmarks/BenchmarkCase.php',
        ));
        $display = $tester->getDisplay();
        $this->assertContains('BenchmarkCase', $display);
    }

    /**
     * It should escape paramters when running in separate process.
     */
    public function testSeparateProcessEscape()
    {
        $this->runCommand('run', array(
            '--processisolation' => 'iteration',
            'path' => __DIR__ . '/../../benchmarks/IsolatedParametersCase.php',
        ));
    }
}
