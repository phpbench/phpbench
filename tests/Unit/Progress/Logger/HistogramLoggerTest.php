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

use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PhpBench\Progress\Logger\HistogramLogger;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Util\TimeUnit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class HistogramLoggerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->output = new BufferedOutput();
        $this->timeUnit = new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MILLISECONDS);

        $this->logger = new HistogramLogger($this->timeUnit);
        $this->logger->setOutput($this->output);
        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->subject = $this->prophesize(Subject::class);
        $this->iteration = $this->prophesize(Iteration::class);
        $this->variant = new Variant(
            $this->subject->reveal(),
            new ParameterSet('one'),
            1,
            0
        );
        $this->variant->spawnIterations(4);
        $this->benchmark->getSubjects()->willReturn([
            $this->subject->reveal(),
        ]);
        $this->benchmark->getClass()->willReturn('BenchmarkTest');

        $this->subject->getName()->willReturn('benchSubject');
        $this->subject->getIndex()->willReturn(1);
        $this->subject->getOutputTimeUnit()->willReturn('milliseconds');
        $this->subject->getOutputMode()->willReturn('time');
        $this->subject->getRetryThreshold()->willReturn(10);
        $this->subject->getOutputTimePrecision()->willReturn(3);
    }

    /**
     * It should show the benchmark name and list all of the subjects.
     */
    public function testBenchmarkStart()
    {
        $this->logger->benchmarkStart($this->benchmark->reveal());
        $display = $this->output->fetch();
        $this->assertStringContainsString('BenchmarkTest', $display);
        $this->assertStringContainsString('#1 benchSubject', $display);
    }

    /**
     * Test iteration start.
     */
    public function testIterationStart()
    {
        $this->iteration->getIndex()->willReturn(1);
        $this->iteration->getVariant()->willReturn($this->variant);
        $this->logger->iterationStart($this->iteration->reveal());
        $display = $this->output->fetch();
        $this->assertStringContainsString('it  1/4', $display);
    }

    /**
     * It should show information at the start of the variant.
     */
    public function testIterationsStart()
    {
        $this->logger->variantStart($this->variant);
        $display = $this->output->fetch();
        $this->assertStringContainsString(
            '1  (σ = 0.000ms ) -2σ [                 ] +2σ',
            $display
        );
        $this->assertStringContainsString(
            'benchSubject',
            $display
        );
        $this->assertStringContainsString(
            'parameter set one',
            $display
        );
    }

    /**
     * It should show an error if the iteration has an exception.
     */
    public function testIterationException()
    {
        $this->variant->setException(new \Exception('foo'));
        $this->logger->variantEnd($this->variant);
        $this->assertStringContainsString('ERROR', $this->output->fetch());
    }

    /**
     * It should show the histogram and statistics when an iteration is
     * completed (and there were no rejections).
     */
    public function testIterationEnd()
    {
        foreach ($this->variant as $iteration) {
            foreach (TestUtil::createResults(10, 10) as $result) {
                $iteration->setResult($result);
            }
        }
        $this->variant->computeStats();

        $this->logger->variantEnd($this->variant);
        $display = $this->output->fetch();
        $this->assertStringContainsString(
            '1  (σ = 0.000ms ) -2σ [        █        ] +2σ [μ Mo]/r: 0.010 0.010 μRSD/r: 0.00%',
            $display
        );
    }
}
