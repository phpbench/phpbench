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
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PhpBench\Progress\Logger\DotsLogger;
use PhpBench\Util\TimeUnit;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Output\OutputInterface;

class DotsLoggerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->tearDown();

        $this->output = $this->prophesize(OutputInterface::class);
        $this->timeUnit = new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MILLISECONDS);
        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->subject = $this->prophesize(Subject::class);
        $this->iteration = $this->prophesize(Iteration::class);
        $this->variant = $this->prophesize(Variant::class);
    }

    protected function tearDown(): void
    {
        putenv('CONTINUOUS_INTEGRATION=0');
    }

    /**
     * It should output a simple . at the end of a subject in CI mode.
     */
    public function testIterationsEndWithCI()
    {
        $logger = $this->createLogger(true);
        $this->output->write('.')->shouldBeCalled();
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->hasFailed()->willReturn(false);
        $this->variant->hasErrorStack()->willReturn(false);
        $logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should reset the line and dump the buffer when NOT in CI mode.
     */
    public function testIterationsEnd()
    {
        $logger = $this->createLogger(false);
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->hasFailed()->willReturn(false);
        $this->variant->hasErrorStack()->willReturn(false);
        $this->output->write("\x0D. ")->shouldBeCalled();
        $logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should log an error.
     */
    public function testIterationsEndException()
    {
        $logger = $this->createLogger(false);
        $this->variant->hasErrorStack()->willReturn(true);
        $this->variant->hasFailed()->willReturn(false);
        $this->variant->getRejectCount()->willReturn(0);
        $this->output->write("\x0D<error>E</error> ")->shouldBeCalled();
        $logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should log a failure.
     */
    public function testIterationsEndFailure()
    {
        $logger = $this->createLogger(false);
        $this->variant->hasErrorStack()->willReturn(false);
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->hasFailed()->willReturn(true);
        $this->output->write("\x0D<error>F</error> ")->shouldBeCalled();
        $logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should return early if the rejection count > 0.
     */
    public function testIterationsEndRejectionsReturnEarly()
    {
        $logger = $this->createLogger(false);
        $this->variant->getRejectCount()->willReturn(5);
        $this->output->write(Argument::any())->shouldNotBeCalled();
        $logger->variantEnd($this->variant->reveal());
    }

    /**
     * It should do nothing in CI mode at the end of an iteration.
     */
    public function testDoNothingCiIterations()
    {
        $logger = $this->createLogger(true);
        $this->output->write(Argument::any())->shouldNotBeCalled();
        $logger->iterationEnd($this->iteration->reveal());
    }

    /**
     * It should show a spinner when not in CI mode.
     */
    public function testIteration()
    {
        $logger = $this->createLogger(false);

        $this->iteration->getIndex()->willReturn(0, 1, 2, 3, 4);

        $this->output->write("\x0D|")->shouldBeCalled();
        $this->output->write("\x0D/")->shouldBeCalled();
        $this->output->write("\x0D-")->shouldBeCalled();
        $this->output->write("\x0D|")->shouldBeCalled();
        $this->output->write("\x0D\\")->shouldBeCalled();

        $logger->iterationStart($this->iteration->reveal());
        $logger->iterationStart($this->iteration->reveal());
        $logger->iterationStart($this->iteration->reveal());
        $logger->iterationStart($this->iteration->reveal());
        $logger->iterationStart($this->iteration->reveal());
    }

    private function createLogger($ci = false)
    {
        putenv('CONTINUOUS_INTEGRATION' . ($ci ? '=1' : '=0'));
        $logger = new DotsLogger($this->timeUnit);
        $logger->setOutput($this->output->reveal());

        return $logger;
    }
}
