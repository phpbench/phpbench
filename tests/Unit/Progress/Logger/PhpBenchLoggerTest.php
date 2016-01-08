<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Progress\Logger;

use PhpBench\Console\OutputAwareInterface;
use Prophecy\Argument;

abstract class PhpBenchLoggerTest extends \PHPUnit_Framework_TestCase
{
    protected $logger;
    protected $output;
    protected $document;
    protected $benchmark;
    protected $iterations;
    protected $subject;
    protected $parameterSet;

    public function setUp()
    {
        $this->benchmark = $this->prophesize('PhpBench\Benchmark\Metadata\BenchmarkMetadata');
        $this->iterations = $this->prophesize('PhpBench\Benchmark\IterationCollection');
        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->parameterSet = $this->prophesize('PhpBench\Benchmark\ParameterSet');
        $this->document = $this->prophesize('PhpBench\Benchmark\SuiteDocument');
        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');

        $this->logger = $this->getLogger();

        if ($this->logger instanceof OutputAwareInterface) {
            $this->logger->setOutput($this->output->reveal());
        }
    }

    abstract public function getLogger();

    /**
     * It should show the PHPBench version.
     */
    public function testStart()
    {
    }

    /**
     * It should show a summary at the end of the suite.
     */
    public function testEndSuite()
    {
        $this->setUpSummary();
        $this->document->hasErrors()->willReturn(false);
        $this->output->writeln(Argument::any())->shouldBeCalled();
        $this->logger->endSuite($this->document->reveal());
    }

    /**
     * It should show errors.
     */
    public function testEndSuiteErrors()
    {
        $this->setUpSummary();
        $this->document->hasErrors()->willReturn(true);
        $this->document->getErrorStacks()->willReturn(array(
            array(
                'subject' => 'Namespace\Foo::bar',
                'exceptions' => array(
                    array(
                        'exception_class' => 'ExceptionOne',
                        'message' => 'MessageOne',
                    ),
                    array(
                        'exception_class' => 'ExceptionTwo',
                        'message' => 'MessageTwo',
                    ),
                ),
            ),
        ));

        $this->output->writeln(Argument::containingString('1 subjects encountered errors'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('Namespace\Foo::bar'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('ExceptionOne'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('ExceptionTwo'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('MessageOne'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('Two'))->shouldBeCalled();
        $this->output->writeln(Argument::any())->shouldBeCalled();

        $this->logger->endSuite($this->document->reveal());
    }

    private function setUpSummary()
    {
        $nbSubjects = 4;
        $nbIterations = 1;
        $nbRevolutions = 2;
        $nbRejects = 3;
        $min = 10;
        $max = 12;
        $mean = 11;
        $mode = 10;
        $totalTime = 123;
        $meanStDev = 321;
        $meanRelStDev = 231;

        $this->document->getNbSubjects()->willReturn($nbSubjects);
        $this->document->getNbIterations()->willReturn($nbIterations);
        $this->document->getNbRevolutions()->willReturn($nbRevolutions);
        $this->document->getNbRejects()->willReturn($nbRejects);
        $this->document->getMinTime()->willReturn($min);
        $this->document->getMeanTime()->willReturn($mean);
        $this->document->getModeTime()->willReturn($mode);
        $this->document->getMaxTime()->willReturn($max);
        $this->document->getTotalTime()->willReturn($totalTime);
        $this->document->getMeanStDev()->willReturn($meanStDev);
        $this->document->getMeanRelStDev()->willReturn($meanRelStDev);
    }
}
