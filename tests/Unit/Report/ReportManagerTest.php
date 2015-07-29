<?php

namespace PhpBench\Tests\Unit\Report;

use PhpBench\Report\ReportManager;
use Prophecy\Argument;

class ReportManagerTest extends \PHPUnit_Framework_TestCase
{
    private $reportManager;
    private $generator;
    private $result;
    private $output;

    public function setUp()
    {
        $this->reportManager = new ReportManager();
        $this->generator = $this->prophesize('PhpBench\ReportGenerator');
        $this->generator->getDefaultReports()->willReturn(array());
        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->result = $this->prophesize('PhpBench\Result\SuiteResult');
    }

    /**
     * Report configurations can be added to it
     * It can retrieve report configurations
     */
    public function testAddReportConfiguration()
    {
        $this->reportManager->addReport('hello', array('goodbye' => 'byegood'));
        $report = $this->reportManager->getReport('hello');
        $this->assertEquals(array('goodbye' => 'byegood'), $report);
    }

    /**
     * It throws an exception when an attempt is made to register two reports with the same name
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddReportDuplicate()
    {
        $this->reportManager->addReport('hello', array('goodbye' => 'byegood'));
        $this->reportManager->addReport('hello', array('goodbye' => 'byegood'));
    }

    /**
     * Report generators can be added to it
     * Report generators can be retrieved from it
     */
    public function testAddReportGenerator()
    {
        $this->reportManager->addGenerator('gen', $this->generator->reveal());
        $this->assertSame($this->generator->reveal(), $this->reportManager->getGenerator('gen'));
    }

    /**
     * It throws an exception when an attempt is made to register to generators with the same name
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddGeneratorTwice()
    {
        $this->reportManager->addGenerator('gen', $this->generator->reveal());
        $this->reportManager->addGenerator('gen', $this->generator->reveal());
    }

    /**
     * It should accept an array of raw JSON strings representing report configurations OR a string representing a generator name, the report configurations should be added and it will return the names of all the reports.
     */
    public function testProcessCliReports()
    {
        $reports = array(
            'foobar',
            '{"param": "one"}',
        );

        $names = $this->reportManager->processCliReports($reports);
        $this->assertCount(2, $names);
        $this->assertEquals('foobar', $names[0]);
        $this->assertNotNull($names[1]);

        $report = $this->reportManager->getReport($names[1]);
        $this->assertNotNull($report);
        $this->assertEquals(array('param' => 'one'), $report);
    }

    /**
     * It should throw an exception with an invalid JSON string
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidJson()
    {
        $this->reportManager->processCliReports(array('{asdasd""'));
    }

    /**
     * It should generate reports
     */
    public function testGenerate()
    {
        $this->reportManager->addGenerator('test', $this->generator->reveal());
        $this->reportManager->addReport('test_report', array('generator' => 'test'));
        $this->generator->generate($this->result->reveal(), array())->shouldBeCalled();
        $this->generator->configure(Argument::type('Symfony\Component\OptionsResolver\OptionsResolver'))->shouldBeCalled();
        $this->reportManager->generateReports(
            $this->output->reveal(),
            $this->result->reveal(),
            array('test_report')
        );
    }

    /**
     * It should inject the output to the report if it implements the OutputAware interface
     */
    public function testGenerateOutputAware()
    {
        $generator = $this->prophesize('PhpBench\ReportGenerator')->willImplement('PhpBench\Console\OutputAware');
        $generator->getDefaultReports()->willReturn(array());

        $this->reportManager->addGenerator('test', $generator->reveal());
        $this->reportManager->addReport('test_report', array('generator' => 'test'));
        $generator->generate($this->result->reveal(), array())->shouldBeCalled();
        $generator->configure(Argument::type('Symfony\Component\OptionsResolver\OptionsResolver'))->shouldBeCalled();
        $generator->setOutput($this->output->reveal())->shouldBeCalled();
        $this->reportManager->generateReports(
            $this->output->reveal(),
            $this->result->reveal(),
            array('test_report')
        );
    }

    /**
     * It should throw an exception if the generator does not return an array from the getDefaultReports method.
     *
     * @expectedException RuntimeException
     */
    public function testDefaultReportsNotArray()
    {
        $generator = $this->prophesize('PhpBench\ReportGenerator');
        $generator->getDefaultReports()->willReturn(new \stdClass);
        $this->reportManager->addGenerator('test', $generator->reveal());

        $this->reportManager->generateReports(
            $this->output->reveal(),
            $this->result->reveal(),
            array('test_report')
        );
    }
}
