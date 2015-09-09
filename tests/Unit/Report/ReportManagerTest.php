<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report;

use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Report\ReportManager;

class ReportManagerTest extends \PHPUnit_Framework_TestCase
{
    private $reportManager;
    private $generator;
    private $suiteDocument;
    private $output;

    public function setUp()
    {
        $this->reportManager = new ReportManager();
        $this->generator = $this->prophesize('PhpBench\ReportGeneratorInterface');
        $this->generator->getDefaultReports()->willReturn(array());
        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->suiteDocument = new SuiteDocument();
        $this->suiteDocument->loadXml('<?xml version="1.0"?><phpbench />');
    }

    /**
     * Report configurations can be added to it
     * It can retrieve report configurations.
     */
    public function testAddReportConfiguration()
    {
        $this->reportManager->addReport('hello', array('goodbye' => 'byegood'));
        $report = $this->reportManager->getReport('hello');
        $this->assertEquals(array('goodbye' => 'byegood'), $report);
    }

    /**
     * It should recursively merge report configurations when the extend each other.
     */
    public function testMergeConfig()
    {
        $this->reportManager->addGenerator('generator', $this->generator->reveal());
        $this->reportManager->addReport('one', array(
            'generator' => 'generator',
            'params' => array(
                'one' => '1',
                'three' => '3',
                'array' => array(
                    'bar' => 'boo',
                    'boo' => 'bar',
                ),
            ),
        ));
        $this->reportManager->addReport('two', array(
            'extends' => 'one',
            'params' => array(
                'two' => '2',
                'three' => '7',
                'array' => array(
                    'bar' => 'baz',
                ),
            ),
        ));
        $this->generator->getDefaultConfig()->willReturn(array(
            'params' => array(),
        ));
        $this->generator->getSchema()->willReturn(new \stdClass());
        $this->generator->generate(
            $this->suiteDocument,
            array(
                'params' => array(
                    'one' => '1',
                    'three' => '7',
                    'array' => array(
                        'bar' => 'baz',
                        'boo' => 'bar',
                    ),
                    'two' => '2',
                ),
            )
        )->shouldBeCalled();

        $this->reportManager->generateReports(
            $this->output->reveal(),
            $this->suiteDocument,
            array('two')
        );
    }

    /**
     * It throws an exception when an attempt is made to register two reports with the same name.
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
     * Report generators can be retrieved from it.
     */
    public function testAddReportGenerator()
    {
        $this->reportManager->addGenerator('gen', $this->generator->reveal());
        $this->assertSame($this->generator->reveal(), $this->reportManager->getGenerator('gen'));
    }

    /**
     * It throws an exception when an attempt is made to register to generators with the same name.
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
     * It should throw an exception with an invalid JSON string.
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidJson()
    {
        $this->reportManager->processCliReports(array('{asdasd""'));
    }

    /**
     * It should generate reports.
     */
    public function testGenerate()
    {
        $this->reportManager->addGenerator('test', $this->generator->reveal());
        $this->reportManager->addReport('test_report', array('generator' => 'test'));
        $this->generator->generate($this->suiteDocument, array())->shouldBeCalled();
        $this->generator->getDefaultConfig()->willReturn(array());
        $this->generator->getSchema()->willReturn(new \stdClass());
        $this->reportManager->generateReports(
            $this->output->reveal(),
            $this->suiteDocument,
            array('test_report')
        );
    }

    /**
     * It should inject the output to the report if it implements the OutputAware interface.
     */
    public function testGenerateOutputAware()
    {
        $generator = $this->prophesize('PhpBench\ReportGeneratorInterface')
            ->willImplement('PhpBench\Console\OutputAwareInterface');
        $generator->getDefaultReports()->willReturn(array());
        $generator->getDefaultConfig()->willReturn(array());
        $generator->getSchema()->willReturn(new \stdClass());

        $this->reportManager->addGenerator('test', $generator->reveal());
        $this->reportManager->addReport('test_report', array('generator' => 'test'));

        $generator->generate($this->suiteDocument, array())->shouldBeCalled();
        $generator->setOutput($this->output->reveal())->shouldBeCalled();

        $this->reportManager->generateReports(
            $this->output->reveal(),
            $this->suiteDocument,
            array('test_report')
        );
    }

    /**
     * It should throw an exception if the configuration does not match the schema.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage is not defined and the definition does not allow additional properties
     */
    public function testInvalidSchema()
    {
        $this->generator->getDefaultReports()->willReturn(array());
        $this->generator->getDefaultConfig()->willReturn(array());
        $this->generator->getSchema()->willReturn(array(
            'type' => 'object',
            'properties' => array(
                'foobar' => array('type' => 'string'),
            ),
            'additionalProperties' => false,
        ));

        $this->reportManager->addGenerator('test', $this->generator->reveal());
        $this->reportManager->addReport('test_report', array(
            'generator' => 'test',
            'barbarboo' => 'tset',
        ));

        $this->reportManager->generateReports(
            $this->output->reveal(),
            $this->suiteDocument,
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
        $generator = $this->prophesize('PhpBench\ReportGeneratorInterface');
        $generator->getDefaultReports()->willReturn(new \stdClass());
        $this->reportManager->addGenerator('test', $generator->reveal());

        $this->reportManager->generateReports(
            $this->output->reveal(),
            $this->suiteDocument,
            array('test_report')
        );
    }
}
