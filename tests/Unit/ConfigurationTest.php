<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit;

use PhpBench\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    private $configuration;

    public function setUp()
    {
        $this->configuration = new Configuration();
    }

    /**
     * It can have report generators added to it.
     */
    public function testAddReportGenerator()
    {
        $generator = $this->prophesize('PhpBench\ReportGenerator');
        $this->configuration->addReportGenerator('name', $generator->reveal());
        $this->assertSame(array('name' => $generator->reveal()), $this->configuration->getReportGenerators());
    }

    /**
     * It can have progress loggers added to it.
     */
    public function testAddProgressLogger()
    {
        $logger = $this->prophesize('PhpBench\ProgressLogger');
        $this->configuration->addProgressLogger('name', $logger->reveal());
        $this->assertSame(
            $logger->reveal(),
            $this->configuration->getProgressLogger('name')
        );
    }

    /**
     * It should throw an exception if an unknown logger is requested.
     *
     * @expectedException PhpBench\Exception\InvalidArgumentException
     * @expectedExceptionMessage Unknown progress logger "unknown", known progress loggers: "name"
     */
    public function testGetProgressLoggerUnknown()
    {
        $logger = $this->prophesize('PhpBench\ProgressLogger');
        $this->configuration->addProgressLogger('name', $logger->reveal());
        $this->configuration->getProgressLogger('unknown');
    }

    /**
     * Its should be able to set the progress logger to use.
     */
    public function testSetProgress()
    {
        $this->configuration->setProgress('hello');
        $this->assertEquals('hello', $this->configuration->getProgress());
    }

    /**
     * It can have the path set on it.
     */
    public function testSetPath()
    {
        $this->configuration->setPath('/foo');
        $this->assertEquals('/foo', $this->configuration->getPath());
    }

    /**
     * It can have report (configurations) added to it.
     */
    public function testAddReport()
    {
        $this->configuration->addReport(array(
            'name' => 'report',
            'foo' => 'bar',
        ));
        $reports = $this->configuration->getReports();

        $this->assertEquals(array(
            array(
            'name' => 'report',
            'foo' => 'bar',
        ), ), $reports);

        return $this->configuration;
    }

    /**
     * It can have the report (configurations) set/replaced.
     *
     * @depends testAddReport
     */
    public function testSetReport(Configuration $configuration)
    {
        $this->assertCount(1, $configuration->getReports());
        $configuration->setReports(array());
        $this->assertCount(0, $configuration->getReports());
    }
}
