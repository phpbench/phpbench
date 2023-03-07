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

use PhpBench\Assertion\VariantAssertionResults;
use PhpBench\Progress\Logger\VerboseLogger;
use PhpBench\Util\TimeUnit;

class VerboseLoggerTest extends PhpBenchLoggerTestCase
{
    public function getLogger()
    {
        $timeUnit = new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MILLISECONDS);

        return new VerboseLogger($this->output, $this->variantFormatter, $timeUnit);
    }

    /**
     * It should output when the benchmark starts.
     */
    public function testBenchmarkStart(): void
    {
        $this->benchmark->getClass()->willReturn('Benchmark');
        $this->logger->benchmarkStart($this->benchmark->reveal());
        self::assertStringContainsString('Benchmark', $this->output->fetch());
    }

    /**
     * It should output at the end of an iteration set.
     */
    public function testIterationsEnd(): void
    {
        $this->variant->hasErrorStack()->willReturn(false);
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->getStats()->willReturn($this->stats->reveal());
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->getAssertionResults()->willReturn(new VariantAssertionResults($this->variant->reveal(), []));
        $this->variant->getParameterSet()->willReturn($this->parameterSet);
        $this->subject->getOutputTimeUnit()->willReturn(null);
        $this->subject->getOutputMode()->willReturn(null);
        $this->subject->getOutputTimePrecision()->willReturn(null);
        $this->subject->getName()->willReturn('benchFoo');

        $this->logger->variantEnd($this->variant->reveal());
        self::assertStringContainsString('summary', $this->output->fetch());
    }
}
