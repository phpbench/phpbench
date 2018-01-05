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

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Assertion\AssertionFailures;
use PhpBench\Assertion\AssertionWarnings;
use PhpBench\Math\Distribution;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Summary;
use PhpBench\Model\Variant;
use PHPUnit\Framework\TestCase;

class SummaryTest extends TestCase
{
    public function setUp()
    {
        $this->suite = $this->prophesize(Suite::class);
        $this->bench1 = $this->prophesize(Benchmark::class);
        $this->subject1 = $this->prophesize(Subject::class);
        $this->variant1 = $this->prophesize(Variant::class);
        $this->stats = $this->prophesize(Distribution::class);
    }

    /**
     * It should provide a summary.
     */
    public function testSummary()
    {
        $this->bench1->getSubjects()->willReturn([$this->subject1->reveal()]);
        $this->subject1->getVariants()->willReturn([$this->variant1->reveal()]);
        $this->variant1->getStats()->willReturn($this->stats->reveal());
        $this->variant1->count()->willReturn(4);
        $this->variant1->getRejectCount()->willReturn(11);
        $this->variant1->hasErrorStack()->willReturn(false);
        $this->variant1->getRevolutions()->willReturn(10);
        $this->variant1->getSubject()->willReturn($this->subject1->reveal());
        $this->variant1->getFailures()->willReturn(new AssertionFailures($this->variant1->reveal()));
        $this->variant1->getWarnings()->willReturn(new AssertionWarnings($this->variant1->reveal()));
        $this->stats->getIterator()->willReturn(new \ArrayIterator([
            'min' => '1',
            'max' => '2',
            'mean' => 5,
            'mode' => 6,
            'sum' => 7,
            'stdev' => 8,
            'rstdev' => 9,

        ]));
        $this->suite->getBenchmarks()->willReturn([$this->bench1]);

        $summary = new Summary($this->suite->reveal());

        $this->assertEquals(1, $summary->getNbSubjects());
        $this->assertEquals(4, $summary->getNbIterations());
        $this->assertEquals(10, $summary->getNbRevolutions());
        $this->assertEquals(1, $summary->getMinTime());
        $this->assertEquals(2, $summary->getMaxTime());
        $this->assertEquals(5, $summary->getMeanTime());
        $this->assertEquals(6, $summary->getModeTime());
        $this->assertEquals(7, $summary->getTotalTime());
        $this->assertEquals(8, $summary->getMeanStDev());
        $this->assertEquals(9, $summary->getMeanRelStDev());
    }
}
