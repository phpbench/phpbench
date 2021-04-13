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

use PhpBench\Assertion\AssertionResult;
use PhpBench\Assertion\VariantAssertionResults;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PhpBench\Progress\Logger\DotsLogger;
use Prophecy\Prophecy\ObjectProphecy;

class DotsLoggerTest extends LoggerTestCase
{
    /**
     * @var ObjectProphecy|Benchmark
     */
    private $benchmark;
    /**
     * @var ObjectProphecy|Subject
     */
    private $subject;
    /**
     * @var ObjectProphecy|Iteration
     */
    private $iteration;
    /**
     * @var ObjectProphecy|Variant
     */
    private $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->subject = $this->prophesize(Subject::class);
        $this->iteration = $this->prophesize(Iteration::class);
        $this->variant = $this->prophesize(Variant::class);
    }

    /**
     * It should reset the line and dump the buffer when NOT in CI mode.
     */
    public function testIterationsEnd(): void
    {
        $logger = $this->createLogger();
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->hasErrorStack()->willReturn(false);
        $this->variant->getAssertionResults()->willReturn(new VariantAssertionResults($this->variant->reveal(), []));
        $logger->variantEnd($this->variant->reveal());
        self::assertEquals("\x0D. ", $this->output->fetch());
    }

    /**
     * It should log an error.
     */
    public function testIterationsEndException(): void
    {
        $logger = $this->createLogger();
        $this->variant->hasErrorStack()->willReturn(true);
        $this->variant->getAssertionResults()->willReturn(new VariantAssertionResults($this->variant->reveal(), []));
        $this->variant->getRejectCount()->willReturn(0);
        $logger->variantEnd($this->variant->reveal());
        self::assertEquals("\x0DE ", $this->output->fetch());
    }

    /**
     * It should log a failure.
     */
    public function testIterationsEndFailure(): void
    {
        $logger = $this->createLogger();
        $this->variant->hasErrorStack()->willReturn(false);
        $this->variant->getRejectCount()->willReturn(0);
        $this->variant->getAssertionResults()->willReturn(new VariantAssertionResults($this->variant->reveal(), [AssertionResult::fail()]));
        $logger->variantEnd($this->variant->reveal());
        self::assertEquals("\x0DF ", $this->output->fetch());
    }

    /**
     * It should return early if the rejection count > 0.
     */
    public function testIterationsEndRejectionsReturnEarly(): void
    {
        $logger = $this->createLogger();
        $this->variant->getRejectCount()->willReturn(5);
        $logger->variantEnd($this->variant->reveal());
        self::assertEquals('', $this->output->fetch());
    }

    /**
     * It should show a spinner when not in CI mode.
     */
    public function testIteration(): void
    {
        $logger = $this->createLogger();

        $this->iteration->getIndex()->willReturn(0, 1, 2, 3, 4);

        $logger->iterationStart($this->iteration->reveal());
        $logger->iterationStart($this->iteration->reveal());
        $logger->iterationStart($this->iteration->reveal());
        $logger->iterationStart($this->iteration->reveal());
        $logger->iterationStart($this->iteration->reveal());
        self::assertEquals("\r|\r/\r-\r\\\r|", $this->output->fetch());
    }

    private function createLogger(): DotsLogger
    {
        $logger = new DotsLogger(
            $this->output,
            $this->variantFormatter,
            $this->timeUnit
        );

        return $logger;
    }
}
