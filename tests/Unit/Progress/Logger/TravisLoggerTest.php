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

use PhpBench\Progress\Logger\TravisLogger;
use PhpBench\Util\TimeUnit;
use Prophecy\Argument;

class TravisLoggerTest extends PhpBenchLoggerTest
{
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
        $this->iterations->getRejectCount()->willReturn(0);
        $this->iterations->hasException()->willReturn(false);
        $this->iterations->count()->willReturn(10);
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

        $this->output->writeln(Argument::containingString('0.001ms'))->shouldBeCalled();
        $this->logger->iterationsEnd($this->iterations->reveal());
    }

    /**
     * It should log errors.
     */
    public function testIterationsEndException()
    {
        $this->iterations->hasException()->willReturn(true);
        $this->iterations->getRejectCount()->willReturn(0);
        $this->iterations->getSubject()->willReturn($this->subject->reveal());
        $this->iterations->count()->willReturn(10);
        $this->subject->getName()->willReturn('benchFoo');

        $this->output->writeln(Argument::containingString('ERROR'))->shouldBeCalled();
        $this->logger->iterationsEnd($this->iterations->reveal());
    }

    /**
     * It should use the subject time unit.
     * It should use the subject mode.
     */
    public function testUseSubjectTimeUnit()
    {
        $this->iterations->getRejectCount()->willReturn(0);
        $this->iterations->hasException()->willReturn(false);
        $this->iterations->getStats()->willReturn(array(
            'mean' => 1.0,
            'mode' => 1.0,
            'stdev' => 2.0,
            'rstdev' => 20.0,
        ));
        $this->iterations->getSubject()->willReturn($this->subject->reveal());
        $this->iterations->count()->willReturn(10);
        $this->iterations->getParameterSet()->willReturn($this->parameterSet->reveal());
        $this->subject->getOutputTimeUnit()->willReturn(TimeUnit::MICROSECONDS);
        $this->subject->getOutputMode()->willReturn(TimeUnit::MODE_THROUGHPUT);
        $this->subject->getName()->willReturn('benchFoo');
        $this->parameterSet->getIndex()->willReturn(0);

        $this->output->writeln(Argument::containingString('1.000ops/μs'))->shouldBeCalled();
        $this->logger->iterationsEnd($this->iterations->reveal());
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
