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

use PhpBench\Progress\Logger\VerboseLogger;
use PhpBench\Util\TimeUnit;
use Prophecy\Argument;

class VerboseLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $timeUnit = new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MILLISECONDS);
        $this->logger = new VerboseLogger($timeUnit);
        $this->logger->setOutput($this->output->reveal());

        $this->benchmark = $this->prophesize('PhpBench\Benchmark\Metadata\BenchmarkMetadata');
        $this->iterations = $this->prophesize('PhpBench\Benchmark\IterationCollection');
        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->parameterSet = $this->prophesize('PhpBench\Benchmark\ParameterSet');
        $this->document = $this->prophesize('PhpBench\Benchmark\SuiteDocument');
    }

    /**
     * It should output when the benchmark starts.
     */
    public function testBenchmarkStart()
    {
        $this->benchmark->getClass()->willReturn('Benchmark');
        $this->output->writeln('<comment>Benchmark</comment>')->shouldBeCalled();
        $this->output->write(PHP_EOL)->shouldBeCalled();
        $this->logger->benchmarkStart($this->benchmark->reveal());
    }

    /**
     * It should output at the end of an iteration set.
     */
    public function testIterationsEnd()
    {
        $this->iterations->hasException()->willReturn(false);
        $this->iterations->getRejectCount()->willReturn(0);
        $this->iterations->getStats()->willReturn(array(
            'mean' => 1.0,
            'mode' => 1.0,
            'stdev' => 2.0,
            'rstdev' => 20.0,
        ));
        $this->iterations->getSubject()->willReturn($this->subject->reveal());
        $this->iterations->getParameterSet()->willReturn($this->parameterSet->reveal());
        $this->subject->getOutputTimeUnit()->willReturn(null);
        $this->subject->getOutputMode()->willReturn(null);
        $this->subject->getName()->willReturn('benchFoo');
        $this->parameterSet->getIndex()->willReturn(0);

        $this->output->write(Argument::containingString('0.001 (ms)'))->shouldBeCalled();
        $this->output->write(PHP_EOL)->shouldBeCalled();
        $this->logger->iterationsEnd($this->iterations->reveal());
    }

    /**
     * It should use the subject time unit.
     * It should use the subject mode.
     */
    public function testUseSubjectTimeUnitAndMode()
    {
        $this->iterations->hasException()->willReturn(false);
        $this->iterations->getRejectCount()->willReturn(0);
        $this->iterations->getStats()->willReturn(array(
            'mean' => 1.0,
            'mode' => 1.0,
            'stdev' => 2.0,
            'rstdev' => 20.0,
        ));
        $this->iterations->getSubject()->willReturn($this->subject->reveal());
        $this->iterations->getParameterSet()->willReturn($this->parameterSet->reveal());
        $this->subject->getOutputTimeUnit()->willReturn(TimeUnit::MICROSECONDS);
        $this->subject->getOutputMode()->willReturn(TimeUnit::MODE_THROUGHPUT);
        $this->subject->getName()->willReturn('benchFoo');
        $this->parameterSet->getIndex()->willReturn(0);

        $this->output->write(Argument::containingString('1.000 (ops/μs)'))->shouldBeCalled();
        $this->output->write(PHP_EOL)->shouldBeCalled();
        $this->logger->iterationsEnd($this->iterations->reveal());
    }

    /**
     * It should log exceptions as ERROR.
     */
    public function testLogError()
    {
        $this->iterations->hasException()->willReturn(true);
        $this->iterations->getSubject()->willReturn($this->subject->reveal());
        $this->subject->getName()->willReturn('benchFoo');
        $this->output->write(Argument::containingString('ERROR'))->shouldBeCalled();
        $this->output->write(PHP_EOL)->shouldBeCalled();
        $this->logger->iterationsEnd($this->iterations->reveal());
    }
}
