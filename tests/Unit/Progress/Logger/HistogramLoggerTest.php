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

use PhpBench\Benchmark\IterationCollection;
use PhpBench\Benchmark\IterationResult;
use PhpBench\Benchmark\ParameterSet;
use PhpBench\Progress\Logger\HistogramLogger;
use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Output\BufferedOutput;

class HistogramLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->output = new BufferedOutput();
        $this->timeUnit = new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MILLISECONDS);

        $this->logger = new HistogramLogger($this->timeUnit);
        $this->logger->setOutput($this->output);
        $this->benchmark = $this->prophesize('PhpBench\Benchmark\Metadata\BenchmarkMetadata');
        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->collection = new IterationCollection(
            $this->subject->reveal(),
            new ParameterSet(),
            4,
            1
        );
        $this->benchmark->getSubjectMetadatas()->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->benchmark->getClass()->willReturn('BenchmarkTest');

        $this->subject->getName()->willReturn('benchSubject');
        $this->subject->getIndex()->willReturn(1);
        $this->subject->getOutputTimeUnit()->willReturn('milliseconds');
        $this->subject->getOutputMode()->willReturn('time');
    }

    /**
     * It should show the benchmark name and list all of the subjects.
     */
    public function testBenchmarkStart()
    {
        $this->logger->benchmarkStart($this->benchmark->reveal());
        $display = $this->output->fetch();
        $this->assertContains('BenchmarkTest', $display);
        $this->assertContains('#1 benchSubject', $display);
    }

    /**
     * It should show information at the start of the iterations.
     */
    public function testIterationsStart()
    {
        $this->logger->iterationsStart($this->collection);
        $display = $this->output->fetch();
        $this->assertContains(
            '1  (σ = 0.000ms ) -2σ [                 ] +2σ',
            $display
        );
        $this->assertContains(
            'benchSubject',
            $display
        );
        $this->assertContains(
            'parameters []',
            $display
        );
    }

    /**
     * It should show an error if the iteration has an exception.
     */
    public function testIterationException()
    {
        $this->collection->setException(new \Exception('foo'));
        $this->logger->iterationsEnd($this->collection);
        $this->assertContains('ERROR', $this->output->fetch());
    }

    /**
     * It should show the histogram and statistics when an iteration is
     * completed (and there were no rejections).
     */
    public function testIterationEnd()
    {
        foreach ($this->collection as $iteration) {
            $iteration->setResult(new IterationResult(10, 10));
        }
        $this->collection->computeStats();

        $this->logger->iterationsEnd($this->collection);
        $this->assertContains(
            '1  (σ = 0.000ms ) -2σ [        █        ] +2σ [μ Mo]/r: 0.010 0.010 μRSD/r: 0.00%',
            $this->output->fetch()
        );
    }
}
