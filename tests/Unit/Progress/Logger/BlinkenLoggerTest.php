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
use PhpBench\Model\Benchmark;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PhpBench\Progress\Logger\BlinkenLogger;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Util\TimeUnit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class BlinkenLoggerTest extends TestCase
{
    const ASSERTION_FAILURE_MESSAGE = 'Failure message';

    /**
     * @var BufferedOutput
     */
    private $output;
    /**
     * @var TimeUnit
     */
    private $timeUnit;
    /**
     * @var BlinkenLogger
     */
    private $logger;
    /**
     * @var ObjectProphecy
     */
    private $benchmark;
    /**
     * @var ObjectProphecy
     */
    private $subject;
    /**
     * @var Variant
     */
    private $variant;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();
        $this->timeUnit = new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MILLISECONDS);

        $this->logger = new BlinkenLogger($this->timeUnit);
        $this->logger->setOutput($this->output);
        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->subject = $this->prophesize(Subject::class);
        $this->variant = new Variant(
            $this->subject->reveal(),
            new ParameterSet('one'),
            10,
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
        $this->subject->getOutputTimePrecision()->willReturn(5);
        $this->subject->getOutputMode()->willReturn('time');
        $this->subject->getRetryThreshold()->willReturn(10);
    }

    /**
     * It should show the benchmark name and list all of the subjects.
     */
    public function testShowAndList()
    {
        $this->logger->benchmarkStart($this->benchmark->reveal());
        $display = $this->output->fetch();
        $this->assertStringContainsString('BenchmarkTest', $display);
        $this->assertStringContainsString('#0 benchSubject', $display);
    }

    /**
     * It should initialize the status line.
     */
    public function testIterationStart()
    {
        $this->logger->iterationStart($this->variant[0]);
        $display = $this->output->fetch();
        $this->assertStringContainsString(
            '0.000',
            $display
        );
    }

    /**
     * It should show information at the start of the variant.
     */
    public function testIterationsStart()
    {
        $this->logger->variantStart($this->variant);
        $display = $this->output->fetch();
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
     * It should show an error if the iteration has an exception.
     */
    public function testIterationFailure()
    {
        foreach ($this->variant as $iteration) {
            $iteration->setResult(new TimeResult(10));
        }
        $this->variant->addFailure(new AssertionFailure(self::ASSERTION_FAILURE_MESSAGE));
        $this->variant->addIteration($iteration);
        $this->variant->computeStats();
        $this->variant->addFailure(new AssertionFailure(self::ASSERTION_FAILURE_MESSAGE));
        $this->logger->variantEnd($this->variant);
        $this->assertStringContainsString('FAIL', $this->output->fetch());
    }

    /**
     * It should show statistics when an iteration is completed (and there
     * were no rejections).
     */
    public function testIterationEndStats()
    {
        foreach ($this->variant as $iteration) {
            foreach (TestUtil::createResults(10, 10) as $result) {
                $iteration->setResult($result);
            }
        }
        $this->variant->computeStats();

        $this->logger->variantEnd($this->variant);
        $this->assertStringContainsString('RSD/r: 0.00%', $this->output->fetch());
    }
}
