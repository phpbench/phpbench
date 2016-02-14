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

use PhpBench\Model\Suite;

class SuiteTest extends \PHPUnit_Framework_TestCase
{
    private $env1;
    private $bench1;

    public function setUp()
    {
        $this->env1 = $this->prophesize('PhpBench\Environment\Information');
        $this->bench1 = $this->prophesize('PhpBench\Model\Benchmark');
        $this->subject1 = $this->prophesize('PhpBench\Model\Subject');
        $this->variant1 = $this->prophesize('PhpBench\Model\Variant');
        $this->iteration1 = $this->prophesize('PhpBench\Model\Iteration');
    }

    /**
     * It should add a benchmark.
     */
    public function testCreateBenchmark()
    {
        $benchmark = $this->createSuite(array())->createBenchmark('FooBench');
        $this->assertInstanceOf('PhpBench\Model\Benchmark', $benchmark);
    }

    /**
     * It should return all of the iterations in the suite.
     * It should return all of the subjects
     * It should return all of the variants.
     */
    public function testGetIterations()
    {
        $this->bench1->getSubjects()->willReturn(array($this->subject1->reveal()));
        $this->subject1->getVariants()->willReturn(array($this->variant1->reveal()));
        $this->variant1->getIterator()->willReturn(new \ArrayIterator(array($this->iteration1->reveal())));

        $suite = $this->createSuite(array(
            $this->bench1->reveal(),
        ), array(
            $this->env1->reveal(),
        ));

        $this->assertSame(array($this->iteration1->reveal()), $suite->getIterations());
        $this->assertSame(array(
            $this->variant1->reveal(),
        ), $suite->getVariants());
        $this->assertSame(array(
            $this->subject1->reveal(),
        ), $suite->getSubjects());
    }

    /**
     * It should return all of the error stacks.
     */
    public function testGetErrorStacks()
    {
        $errorStack = $this->prophesize('PhpBench\Model\ErrorStack');
        $this->bench1->getSubjects()->willReturn(array($this->subject1->reveal()));
        $this->subject1->getVariants()->willReturn(array($this->variant1->reveal()));
        $this->variant1->hasErrorStack()->willReturn(true);
        $this->variant1->getErrorStack()->willReturn($errorStack->reveal());

        $suite = $this->createSuite(array(
            $this->bench1->reveal(),
        ), array(
            $this->env1->reveal(),
        ));

        $this->assertSame(array(
            $errorStack->reveal(),
        ), $suite->getErrorStacks());
    }

    /**
     * It should return the summary.
     */
    public function testGetSummary()
    {
        $errorStack = $this->prophesize('PhpBench\Model\ErrorStack');
        $this->bench1->getSubjects()->willReturn(array($this->subject1->reveal()));
        $this->subject1->getVariants()->willReturn(array($this->variant1->reveal()));
        $this->variant1->hasErrorStack()->willReturn(true);
        $this->variant1->count()->willReturn(1);
        $this->variant1->getSubject()->wilLReturn($this->subject1->reveal());
        $this->variant1->getRevolutions()->willReturn(10);
        $this->variant1->getRejectCount()->willReturn(0);
        $this->variant1->getRejectCount()->willReturn(0);
        $this->variant1->getErrorStack()->willReturn($errorStack->reveal());

        $suite = $this->createSuite(array(
            $this->bench1->reveal(),
        ), array(
            $this->env1->reveal(),
        ));

        $summary = $suite->getSummary();
        $this->assertInstanceOf('PhpBench\Model\Summary', $summary);
    }

    private function createSuite(array $benchmarks = array(), array $informations = array())
    {
        return new Suite(
            'context',
            new \DateTime('2016-01-25'),
            'path/to/config',
            $benchmarks,
            $informations
        );
    }
}
