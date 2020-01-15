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

use PhpBench\Progress\Logger\TravisLogger;
use PhpBench\Util\TimeUnit;
use Prophecy\Argument;

class TravisLoggerTest extends PhpBenchLoggerTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getLogger()
    {
        $timeUnit = new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MILLISECONDS);

        return new TravisLogger($timeUnit);
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
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->hasErrorStack()->willReturn(false);
        $this->variant->count()->willReturn(10);
        $this->variant->getStats()->willReturn($this->stats->reveal());
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->getParameterSet()->willReturn($this->parameterSet->reveal());
        $this->variant->hasFailed()->willReturn(false);
        $this->subject->getOutputTimeUnit()->willReturn(null);
        $this->subject->getOutputMode()->willReturn(null);
        $this->subject->getName()->willReturn('benchFoo');
        $this->subject->getVariants()->willReturn([$this->variant->reveal()]);
        $this->subject->getOutputTimePrecision()->willReturn(null);
        $this->parameterSet->getIndex()->willReturn(0);

        $this->output->writeln(Argument::containingString('0.001 (ms)'))->shouldBeCalled();
        $this->logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should log errors.
     */
    public function testIterationsEndException()
    {
        $this->variant->hasErrorStack()->willReturn(true);
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->count()->willReturn(10);
        $this->variant->hasFailed()->willReturn(false);
        $this->subject->getName()->willReturn('benchFoo');

        $this->output->writeln(Argument::containingString('ERROR'))->shouldBeCalled();
        $this->logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should use the subject time unit.
     * It should use the subject mode.
     */
    public function testUseSubjectTimeUnit()
    {
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->hasErrorStack()->willReturn(false);
        $this->variant->getStats()->willReturn($this->stats->reveal());
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->count()->willReturn(10);
        $this->variant->getParameterSet()->willReturn($this->parameterSet->reveal());
        $this->variant->hasFailed()->willReturn(false);
        $this->subject->getVariants()->willReturn([$this->variant->reveal()]);
        $this->subject->getOutputTimeUnit()->willReturn(TimeUnit::MICROSECONDS);
        $this->subject->getOutputTimePrecision()->willReturn(null);
        $this->subject->getOutputMode()->willReturn(TimeUnit::MODE_THROUGHPUT);
        $this->subject->getName()->willReturn('benchFoo');
        $this->parameterSet->getIndex()->willReturn(0);

        $this->output->writeln(Argument::containingString('1.000 (ops/μs)'))->shouldBeCalled();
        $this->logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should output an empty line at the end of the suite.
     */
    public function testEndSuite()
    {
        $this->output->write(PHP_EOL)->shouldBeCalled();
        parent::testEndSuite();
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
