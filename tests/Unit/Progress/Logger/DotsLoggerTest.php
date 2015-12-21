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

use PhpBench\Progress\Logger\DotsLogger;
use PhpBench\Util\TimeFormatter;
use PhpBench\Util\TimeUnit;
use Prophecy\Argument;

class DotsLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tearDown();

        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->timeFormatter = new TimeFormatter(new TimeUnit(TimeUnit::MICROSECONDS, TimeUnit::MILLISECONDS));
        $this->benchmark = $this->prophesize('PhpBench\Benchmark\Metadata\BenchmarkMetadata');
        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->iteration = $this->prophesize('PhpBench\Benchmark\Iteration');
    }

    public function tearDown()
    {
        putenv('CONTINUOUS_INTEGRATION=0');
    }

    /**
     * It should output a simple . at the end of a subject in CI mode.
     */
    public function testSubjectEndWithCI()
    {
        $logger = $this->createLogger(true);
        $this->output->write('.')->shouldBeCalled();
        $logger->subjectEnd($this->subject->reveal());
    }

    /**
     * It should reset the line and dump the buffer when NOT in CI mode.
     */
    public function testSubjectEnd()
    {
        $logger = $this->createLogger(false);
        $this->output->write("\x0D. ")->shouldBeCalled();
        $logger->subjectEnd($this->subject->reveal());
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
        $logger = new DotsLogger($this->timeFormatter);
        $logger->setOutput($this->output->reveal());

        return $logger;
    }
}
