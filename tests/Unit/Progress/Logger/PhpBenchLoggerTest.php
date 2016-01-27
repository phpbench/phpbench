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
    protected $variant;
    protected $subject;
    protected $parameterSet;
    protected $stats;

    public function setUp()
    {
        $this->suite = $this->prophesize('PhpBench\Model\Suite');
        $this->summary = $this->prophesize('PhpBench\Model\Summary');
        $this->benchmark = $this->prophesize('PhpBench\Model\Benchmark');
        $this->variant = $this->prophesize('PhpBench\Model\Variant');
        $this->subject = $this->prophesize('PhpBench\Model\Subject');
        $this->parameterSet = $this->prophesize('PhpBench\Model\ParameterSet');
        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->stats = $this->prophesize('PhpBench\Math\Distribution');

        $this->logger = $this->getLogger();

        if ($this->logger instanceof OutputAwareInterface) {
            $this->logger->setOutput($this->output->reveal());
        }

        $this->suite->getSummary()->willReturn($this->summary->reveal());

        $this->stats->getMean()->willReturn(1.0);
        $this->stats->getMode()->willReturn(1.0);
        $this->stats->getStdev()->willReturn(2.0);
        $this->stats->getRstdev()->willReturn(20);
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
        $this->suite->getErrorStacks()->willReturn(array());
        $this->output->writeln(Argument::any())->shouldBeCalled();
        $this->logger->endSuite($this->suite->reveal());
    }

    /**
     * It should show errors.
     */
    public function testEndSuiteErrors()
    {
        $error1 = $this->prophesize('PhpBench\Model\Error');
        $error1->getMessage()->willReturn('MessageOne');
        $error1->getClass()->willReturn('ExceptionOne');
        $error1->getTrace()->willReturn('-- trace --');

        $error2 = $this->prophesize('PhpBench\Model\Error');
        $error2->getMessage()->willReturn('MessageTwo');
        $error2->getClass()->willReturn('ExceptionTwo');
        $error2->getTrace()->willReturn('-- trace --');
        $errorStack = $this->prophesize('PhpBench\Model\ErrorStack');
        $errorStack->getVariant()->willReturn($this->variant->reveal());
        $errorStack->getIterator()->willReturn(new \ArrayIterator(array($error1->reveal(), $error2->reveal())));

        $this->setUpSummary();
        $this->suite->getErrorStacks()->willReturn(array($errorStack));
        $errorStack->getVariant()->willReturn($this->variant->reveal());
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->subject->getBenchmark()->willReturn($this->benchmark->reveal());
        $this->subject->getName()->willReturn('bar');
        $this->benchmark->getClass()->willReturn('Namespace\Foo');

        $this->output->writeln(Argument::containingString('1 subjects encountered errors'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('Namespace\Foo::bar'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('ExceptionOne'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('ExceptionTwo'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('MessageOne'))->shouldBeCalled();
        $this->output->writeln(Argument::containingString('Two'))->shouldBeCalled();
        $this->output->writeln(Argument::any())->shouldBeCalled();

        $this->logger->endSuite($this->suite->reveal());
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

        $this->summary->getNbSubjects()->willReturn($nbSubjects);
        $this->summary->getNbIterations()->willReturn($nbIterations);
        $this->summary->getNbRevolutions()->willReturn($nbRevolutions);
        $this->summary->getNbRejects()->willReturn($nbRejects);
        $this->summary->getMinTime()->willReturn($min);
        $this->summary->getMeanTime()->willReturn($mean);
        $this->summary->getModeTime()->willReturn($mode);
        $this->summary->getMaxTime()->willReturn($max);
        $this->summary->getTotalTime()->willReturn($totalTime);
        $this->summary->getMeanStDev()->willReturn($meanStDev);
        $this->summary->getMeanRelStDev()->willReturn($meanRelStDev);
    }
}
