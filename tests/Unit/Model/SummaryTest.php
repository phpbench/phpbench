<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Model\Summary;

class SummaryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->suite = $this->prophesize('PhpBench\Model\Suite');
        $this->bench1 = $this->prophesize('PhpBench\Model\Benchmark');
        $this->subject1 = $this->prophesize('PhpBench\Model\Subject');
        $this->variant1 = $this->prophesize('PhpBench\Model\Variant');
        $this->stats = $this->prophesize('PhpBench\Math\Distribution');
    }

    /**
     * It should provide a summary.
     */
    public function testSummary()
    {
        $this->bench1->getSubjects()->willReturn(array($this->subject1->reveal()));
        $this->subject1->getVariants()->willReturn(array($this->variant1->reveal()));
        $this->variant1->getStats()->willReturn($this->stats->reveal());
        $this->variant1->count()->wilLReturn(4);
        $this->variant1->getRejectCount()->wilLReturn(11);
        $this->variant1->hasErrorStack()->wilLReturn(false);
        $this->subject1->getRevs()->willReturn(10);
        $this->variant1->getSubject()->willReturn($this->subject1->reveal());
        $this->stats->getIterator()->willReturn(new \ArrayIterator(array(
            'min' => '1',
            'max' => '2',
            'mean' => 5,
            'mode' => 6,
            'sum' => 7,
            'stdev' => 8,
            'rstdev' => 9,

        )));
        $this->suite->getBenchmarks()->willReturn(array($this->bench1));

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
