<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Unit\Progress\Logger;

use PhpBench\Assertion\AssertionFailure;
use PhpBench\Assertion\AssertionFailures;
use PhpBench\Assertion\AssertionWarning;
use PhpBench\Assertion\AssertionWarnings;
use PhpBench\Console\OutputAwareInterface;
use PhpBench\Math\Distribution;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Error;
use PhpBench\Model\ErrorStack;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Summary;
use PhpBench\Model\Variant;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

abstract class PhpBenchLoggerTest extends TestCase
{
    protected $logger;
    protected $output;
    protected $document;
    protected $benchmark;
    protected $variant;
    protected $subject;
    protected $parameterSet;
    protected $stats;

    protected function setUp(): void
    {
        $this->suite = $this->prophesize(Suite::class);
        $this->summary = $this->prophesize(Summary::class);
        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->variant = $this->prophesize(Variant::class);
        $this->subject = $this->prophesize(Subject::class);
        $this->parameterSet = $this->prophesize(ParameterSet::class);
        $this->output = $this->prophesize(OutputInterface::class);
        $this->stats = $this->prophesize(Distribution::class);

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
        $this->addToAssertionCount(1);
    }

    /**
     * It should show a summary at the end of the suite.
     */
    public function testEndSuite()
    {
        $this->setUpSummary();
        $this->suite->getFailures()->willReturn([]);
        $this->suite->getWarnings()->willReturn([]);
        $this->suite->getErrorStacks()->willReturn([]);
        $this->output->writeln(Argument::any())->shouldBeCalled();
        $this->logger->endSuite($this->suite->reveal());
    }

    public function testEndSuiteErrors()
    {
        $error1 = $this->prophesize(Error::class);
        $error1->getMessage()->willReturn('MessageOne');
        $error1->getClass()->willReturn('ExceptionOne');
        $error1->getTrace()->willReturn('-- trace --');

        $error2 = $this->prophesize(Error::class);
        $error2->getMessage()->willReturn('MessageTwo');
        $error2->getClass()->willReturn('ExceptionTwo');
        $error2->getTrace()->willReturn('-- trace --');
        $errorStack = $this->prophesize(ErrorStack::class);
        $errorStack->getVariant()->willReturn($this->variant->reveal());
        $errorStack->getIterator()->willReturn(new \ArrayIterator([$error1->reveal(), $error2->reveal()]));

        $this->setUpSummary();
        $this->suite->getFailures()->willReturn([]);
        $this->suite->getWarnings()->willReturn([]);
        $this->suite->getErrorStacks()->willReturn([$errorStack]);
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

    public function testEndSuiteFailures()
    {
        $failure1 = new AssertionFailure('Failed!');
        $failure2 = new AssertionFailure('Failed!');
        $failures = new AssertionFailures($this->variant->reveal(), [$failure1, $failure2]);

        $this->setUpSummary();
        $this->suite->getFailures()->willReturn([$failures]);
        $this->suite->getWarnings()->willReturn([]);
        $this->suite->getErrorStacks()->willReturn([]);
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->getParameterSet()->willReturn(new ParameterSet('one',[]));
        $this->subject->getBenchmark()->willReturn($this->benchmark->reveal());
        $this->subject->getName()->willReturn('bar');
        $this->benchmark->getClass()->willReturn('Namespace\Foo');

        $this->output->writeln(Argument::containingString('1 variants failed'))->shouldBeCalled();
        $this->output->writeln(Argument::any())->shouldBeCalled();
        $this->output->write(Argument::any())->shouldBeCalled();

        $this->logger->endSuite($this->suite->reveal());
    }

    public function testEndSuiteWarnings()
    {
        $warning1 = new AssertionWarning('Failed!');
        $warning2 = new AssertionWarning('Failed!');
        $warnings = new AssertionWarnings($this->variant->reveal(), [$warning1, $warning2]);

        $this->setUpSummary();
        $this->suite->getFailures()->willReturn([]);
        $this->suite->getWarnings()->willReturn([$warnings]);
        $this->suite->getErrorStacks()->willReturn([]);
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->getParameterSet()->willReturn(new ParameterSet('one',[]));
        $this->subject->getBenchmark()->willReturn($this->benchmark->reveal());
        $this->subject->getName()->willReturn('bar');
        $this->benchmark->getClass()->willReturn('Namespace\Foo');

        $this->output->writeln(Argument::containingString('1 variants have warnings'))->shouldBeCalled();
        $this->output->writeln(Argument::any())->shouldBeCalled();
        $this->output->write(Argument::any())->shouldBeCalled();

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
        $nbFailures = 0;
        $nbWarnings = 0;

        $this->summary->getNbSubjects()->willReturn($nbSubjects);
        $this->summary->getNbIterations()->willReturn($nbIterations);
        $this->summary->getNbRevolutions()->willReturn($nbRevolutions);
        $this->summary->getNbRejects()->willReturn($nbRejects);
        $this->summary->getNbFailures()->willReturn($nbFailures);
        $this->summary->getNbWarnings()->willReturn($nbWarnings);
        $this->summary->getMinTime()->willReturn($min);
        $this->summary->getMeanTime()->willReturn($mean);
        $this->summary->getModeTime()->willReturn($mode);
        $this->summary->getMaxTime()->willReturn($max);
        $this->summary->getTotalTime()->willReturn($totalTime);
        $this->summary->getMeanStDev()->willReturn($meanStDev);
        $this->summary->getMeanRelStDev()->willReturn($meanRelStDev);
    }
}
