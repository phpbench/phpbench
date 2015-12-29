<?php

namespace PhpBench\Tests\Unit\Console\Command\Handler;

use PhpBench\Console\Command\Handler\ReportHandler;
use PhpBench\Benchmark\SuiteDocument;
use Prophecy\Argument;

class ReportHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $handler;
    private $output;
    private $manager;
    private $document;

    public function setUp()
    {
        $this->manager = $this->prophesize('PhpBench\Report\ReportManager');
        $this->handler = new ReportHandler(
            $this->manager->reveal()
        );
        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->document = new SuiteDocument();
    }

    /**
     * It should accept an array of raw JSON strings representing report configurations OR a string representing a generator name, the report configurations should be added and it will return the names of all the reports.
     */
    public function testProcessCliReports()
    {
        $input = $this->getInput(array(), array(
            'report' => array(
                'foobar',
                '{"param": "one"}',
            ),
            'output' => array(
                'console'
            ),
        ));

        $this->manager->addReport(Argument::type('string'), array('param' => 'one'))->shouldBeCalled();
        $this->manager->renderReports(
            $this->output->reveal(),
            Argument::type('PhpBench\Benchmark\SuiteDocument'),
            Argument::type('array'),
            array('console')
        )->shouldBeCalled();

        $this->handler->reportsFromInput(
            $input->reveal(),
            $this->output->reveal(),
            $this->document
        );
    }

    /**
     * It should throw an exception with an invalid JSON string.
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidJson()
    {
        $input = $this->getInput(array(), array(
            'report' => array(
                '{"param": "one}',
            ),
            'output' => array(
                'console'
            ),
        ));

        $this->handler->reportsFromInput(
            $input->reveal(),
            $this->output->reveal(),
            $this->document
        );
    }

    private function getInput(array $args, array $options)
    {
        $input = $this->prophesize('Symfony\Component\Console\Input\InputInterface');

        foreach ($args as $name => $value) {
            $input->getArgument($name)->willReturn($value);
        }

        foreach ($options as $name => $value) {
            $input->getOption($name)->willReturn($value);
        }

        return $input;
    }
}
