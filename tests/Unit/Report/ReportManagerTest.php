<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report;

use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Dom\Document;
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
        $this->generator = $this->prophesize('PhpBench\Report\GeneratorInterface');
        $this->generator->getDefaultReports()->willReturn(array());
        $this->renderer = $this->prophesize('PhpBench\Report\RendererInterface');
        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->suiteDocument = new SuiteDocument();
        $this->suiteDocument->loadXml('<?xml version="1.0"?><phpbench />');
        $this->reportsDocument = new Document();
        $reportsEl = $this->reportsDocument->createRoot('reports');
        $reportEl = $reportsEl->appendElement('report');
        $reportEl->appendElement('description');
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
     * Outut configurations can be added to it
     * It can retrieve output configurations.
     */
    public function testAddOutputConfiguration()
    {
        $this->reportManager->addOutput('hello', array('goodbye' => 'byegood'));
        $output = $this->reportManager->getOutput('hello');
        $this->assertEquals(array('goodbye' => 'byegood'), $output);
    }

    /**
     * It should throw an exception if adding an already existing output configuration.
     * 
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Output with name
     */
    public function testAddExistingOutputConfiguration()
    {
        $this->reportManager->addOutput('hello', array('goodbye' => 'byegood'));
        $this->reportManager->addOutput('hello', array('goodbye' => 'byegood'));
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
        $this->generator->getSchema()->willReturn(array(
            'type' => 'object',
            'properties' => array(
                'params' => array('type' => 'object'),
            ),
        ));
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
        )->willReturn($this->reportsDocument);

        $this->reportManager->generateReports(
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
     * It can have renderers added to it
     * It can retrieve renderers.
     */
    public function testAddRenderer()
    {
        $this->renderer->getDefaultOutputs()->willReturn(array());
        $this->reportManager->addRenderer('html', $this->renderer->reveal());
        $this->assertSame($this->renderer->reveal(), $this->reportManager->getRenderer('html'));
    }

    /**
     * It should add the renderers default reports.
     */
    public function testAddRendererDefaultReports()
    {
        $this->renderer->getDefaultOutputs()->willReturn(array(
            'html' => array(),
            'foobar' => array(),
        ));
        $this->reportManager->addRenderer('html', $this->renderer->reveal());
        $output = $this->reportManager->getOutput('html');
        $this->assertInternalType('array', $output);
        $this->assertArrayHasKey('renderer', $output);
        $this->assertEquals('html', $output['renderer']);
    }

    /**
     * It should throw an exception if an unknown renderer is requested.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage renderer
     */
    public function testGetRendererUnknown()
    {
        $this->reportManager->getRenderer('unknown');
    }

    /**
     * It should throw an exception if a renderer with the same name already exists.
     *
     * @expectedException InvalidArgumentException
     */
    public function testAddRendererTwice()
    {
        $this->renderer->getDefaultOutputs()->willReturn(array());
        $this->reportManager->addRenderer('html', $this->renderer->reveal());
        $this->reportManager->addRenderer('html', $this->renderer->reveal());
    }

    /**
     * It should render a report.
     */
    public function testRender()
    {
        $this->renderer->getDefaultOutputs()->willReturn(array());
        $this->reportManager->addRenderer('console', $this->renderer->reveal());
        $this->reportManager->addGenerator('test', $this->generator->reveal());
        $this->reportManager->addOutput('console_output', array('renderer' => 'console'));
        $this->reportManager->addReport('test_report', array('generator' => 'test'));

        $this->generator->generate($this->suiteDocument, array())->willReturn($this->reportsDocument);
        $this->generator->getDefaultConfig()->willReturn(array());
        $this->generator->getSchema()->willReturn(array());

        $this->renderer->getDefaultConfig()->willReturn(array());
        $this->renderer->getSchema()->willReturn(array());
        $this->renderer->render($this->reportsDocument, array())->shouldBeCalled();

        $this->reportManager->renderReports(
            $this->output->reveal(),
            $this->suiteDocument,
            array('test_report'),
            array('console_output')
        );

        return $this->reportManager;
    }

    /**
     * It should throw an exception if the output config does not contain the generator key.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage include a "renderer"
     * @depends testRender
     */
    public function testRendererNoRendererKey($reportManager)
    {
        $reportManager->addOutput('invalid', array());

        $reportManager->renderReports(
            $this->output->reveal(),
            $this->suiteDocument,
            array('test_report'),
            array('console_output', 'invalid')
        );
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
        $this->generator->generate($this->suiteDocument, array())->willReturn($this->reportsDocument);
        $this->generator->getDefaultConfig()->willReturn(array());
        $this->generator->getSchema()->willReturn(array());
        $this->reportManager->generateReports(
            $this->suiteDocument,
            array('test_report')
        );
    }

    /**
     * It should throw an exception if the generator returns a non-array schema.
     */
    public function testSchemaIsNotAnArray()
    {
        try {
            $this->reportManager->addGenerator('test', $this->generator->reveal());
            $this->reportManager->addReport('test_report', array('generator' => 'test'));
            $this->generator->getDefaultConfig()->willReturn(array());
            $this->generator->getSchema()->willReturn(new \stdClass());
            $this->reportManager->generateReports(
                $this->suiteDocument,
                array('test_report')
            );
        } catch (\Exception $e) {
            $this->assertContains('Could not generate report "test_report"', $e->getMessage());
            $this->assertInstanceOf('InvalidArgumentException', $e->getPrevious());
            $this->assertContains('must return the JSON schema', $e->getPrevious()->getMessage());
            return;
        }

        $this->fail('Did not throw exception');
    }

    /**
     * It should inject the output to the report if it implements the OutputAware interface.
     */
    public function testGenerateOutputAware()
    {
        $generator = $this->prophesize('PhpBench\Report\GeneratorInterface')
            ->willImplement('PhpBench\Console\OutputAwareInterface');
        $generator->getDefaultReports()->willReturn(array());
        $generator->getDefaultConfig()->willReturn(array());
        $generator->getSchema()->willReturn(array());

        $this->reportManager->addGenerator('test', $generator->reveal());
        $this->reportManager->addReport('test_report', array('generator' => 'test'));

        $generator->generate($this->suiteDocument, array())->willReturn($this->reportsDocument);

        $this->reportManager->generateReports(
            $this->suiteDocument,
            array('test_report')
        );
    }

    /**
     * It should throw an exception if the configuration does not match the schema.
     */
    public function testInvalidSchema()
    {
        try {
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
                $this->suiteDocument,
                array('test_report')
            );
        } catch (\Exception $e) {
            $this->assertContains('Could not generate report "test_report"', $e->getMessage());
            $this->assertInstanceOf('InvalidArgumentException', $e->getPrevious());
            $this->assertContains('is not defined and the definition does not allow additional properties', $e->getPrevious()->getMessage());
            return;
        }

        $this->fail('Did not throw exception');
    }

    /**
     * It should throw an exception if the generator does not return an array from the getDefaultReports method.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage must return an array
     */
    public function testDefaultReportsNotArray()
    {
        $generator = $this->prophesize('PhpBench\Report\GeneratorInterface');
        $generator->getDefaultReports()->willReturn(new \stdClass());
        $this->reportManager->addGenerator('test', $generator->reveal());

        $this->reportManager->generateReports(
            $this->suiteDocument,
            array('test_report')
        );
    }
}
