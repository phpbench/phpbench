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

use PhpBench\Progress\Logger\VerboseLogger;
use PhpBench\Util\TimeUnit;
use Prophecy\Argument;

class VerboseLoggerTest extends PhpBenchLoggerTest
{
    public function getLogger()
    {
        $timeUnit = new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MILLISECONDS);

        return new VerboseLogger($timeUnit);
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
        $this->variant->hasErrorStack()->willReturn(false);
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->getStats()->willReturn($this->stats->reveal());
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->hasFailed()->willReturn(false);
        $this->variant->getParameterSet()->willReturn($this->parameterSet->reveal());
        $this->subject->getOutputTimeUnit()->willReturn(null);
        $this->subject->getOutputMode()->willReturn(null);
        $this->subject->getOutputTimePrecision()->willReturn(null);
        $this->subject->getName()->willReturn('benchFoo');
        $this->parameterSet->getIndex()->willReturn(0);

        $this->output->write(Argument::containingString('0.001 (ms)'))->shouldBeCalled();
        $this->output->write(PHP_EOL)->shouldBeCalled();
        $this->logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should use the subject time unit.
     * It should use the subject mode.
     */
    public function testUseSubjectTimeUnitAndMode()
    {
        $this->variant->hasErrorStack()->willReturn(false);
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->getStats()->willReturn($this->stats->reveal());
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->getParameterSet()->willReturn($this->parameterSet->reveal());
        $this->variant->hasFailed()->willReturn(false);
        $this->subject->getOutputTimeUnit()->willReturn(TimeUnit::MICROSECONDS);
        $this->subject->getOutputMode()->willReturn(TimeUnit::MODE_THROUGHPUT);
        $this->subject->getOutputTimePrecision()->willReturn(null);
        $this->subject->getName()->willReturn('benchFoo');
        $this->parameterSet->getIndex()->willReturn(0);

        $this->output->write(Argument::containingString('1.000 (ops/μs)'))->shouldBeCalled();
        $this->output->write(PHP_EOL)->shouldBeCalled();
        $this->logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should show failures.
     */
    public function testShowFailures()
    {
        $this->variant->hasErrorStack()->willReturn(false);
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->getStats()->willReturn($this->stats->reveal());
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->getParameterSet()->willReturn($this->parameterSet->reveal());
        $this->variant->hasFailed()->willReturn(true);
        $this->subject->getOutputTimeUnit()->willReturn(TimeUnit::MICROSECONDS);
        $this->subject->getOutputMode()->willReturn(TimeUnit::MODE_THROUGHPUT);
        $this->subject->getOutputTimePrecision()->willReturn(null);
        $this->subject->getName()->willReturn('benchFoo');
        $this->parameterSet->getIndex()->willReturn(0);

        $this->output->write(Argument::containingString('<error>'))->shouldBeCalled();
        $this->output->write(PHP_EOL)->shouldBeCalled();
        $this->logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should log exceptions as ERROR.
     */
    public function testLogError()
    {
        $this->variant->hasErrorStack()->willReturn(true);
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->hasFailed()->willReturn(false);
        $this->subject->getName()->willReturn('benchFoo');
        $this->output->write(Argument::containingString('ERROR'))->shouldBeCalled();
        $this->output->write(PHP_EOL)->shouldBeCalled();
        $this->logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should output an empty line at the end of the suite.
     */
    public function testEndSuiteErrors()
    {
        $this->output->write(PHP_EOL)->shouldBeCalled();
        parent::testEndSuiteErrors();
    }
}
