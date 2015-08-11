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
use BenchmarkBench;

/**
 * @beforeMethod setUp
 * @afterMethod setUp
 */
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
     * It should show the default (simple) report.
     */
    public function testCommand()
    {
        $tester = $this->runCommand('run', array(
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
    }

    /**
     * It should run and generate a named report.
     */
    public function testCommandWithReport()
    {
        $tester = $this->runCommand('run', array(
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
            '--report' => array('default'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('bench', $display);
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
            '--report' => array('default'),
        ));
    }

    /**
     * It should run and generate a report configuration.
     */
    public function testCommandWithReportConfiguration()
    {
        $tester = $this->runCommand('run', array(
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
            '--report' => array('{"extends": "default"}'),
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('benchParameterized', $display);
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
            '--report' => array('{"generator": "foo_console_table"}'),
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('benchParameterized', $display);
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
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('benchParameterized', $display);
    }

    /**
     * It should dump to an XML file.
     */
    public function testDumpXml()
    {
        $tester = $this->runCommand('run', array(
            '--dump-file' => self::TEST_FNAME,
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
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
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $dom = new \DOMDocument();
        $dom->loadXml($display);
        $xpath = new \DOMXPath($dom);
        $dom->formatOutput = true;
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
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
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
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
        ));
    }

    /**
     * Its should allow the number of iterations to be specified.
     */
    public function testOverrideIterations()
    {
        $tester = $this->runCommand('run', array(
            '--subject' => array('benchRandom'),
            '--dump' => true,
            '--iterations' => 10,
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
        ));

        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $dom = new \DOMDocument();
        $dom->loadXml($display);
        $dom->formatOutput = true;
        $xpath = new \DOMXPath($dom);
        $parameters = $xpath->query('//subject[@name="benchRandom"]//iteration');
        $this->assertEquals(10, $parameters->length);
    }

    /**
     * It can have the progress logger specified.
     */
    public function testProgressLogger()
    {
        $tester = $this->runCommand('run', array(
            '--progress' => 'benchdots',
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
        ));
        $display = $tester->getDisplay();
        $this->assertContains('BenchmarkBench', $display);
    }

    /**
     * It should run specified groups.
     */
    public function testGroups()
    {
        $tester = $this->runCommand('run', array(
            '--group' => array('do_nothing'),
            '--dump' => true,
            'path' => __DIR__ . '/../../benchmarks/BenchmarkBench.php',
        ));

        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $dom = new \DOMDocument();
        $dom->loadXml($display);
        $dom->formatOutput = true;
        $xpath = new \DOMXPath($dom);
        $parameters = $xpath->query('//subject');
        $this->assertEquals(1, $parameters->length);
    }
}
