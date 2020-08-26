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
use PhpBench\Environment\Information;
use PhpBench\Model\Benchmark;
use PhpBench\Model\ErrorStack;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PHPUnit\Framework\TestCase;

class SuiteTest extends TestCase
{
    private $env1;
    private $bench1;

    protected function setUp(): void
    {
        $this->env1 = $this->prophesize(Information::class);
        $this->bench1 = $this->prophesize(Benchmark::class);
        $this->subject1 = $this->prophesize(Subject::class);
        $this->variant1 = $this->prophesize(Variant::class);
        $this->iteration1 = $this->prophesize(Iteration::class);
    }

    /**
     * It should add a benchmark.
     */
    public function testCreateBenchmark()
    {
        $benchmark = $this->createSuite([])->createBenchmark('FooBench');
        $this->assertInstanceOf('PhpBench\Model\Benchmark', $benchmark);
    }

    /**
     * It should return all of the iterations in the suite.
     * It should return all of the subjects
     * It should return all of the variants.
     */
    public function testGetIterations()
    {
        $this->bench1->getSubjects()->willReturn([$this->subject1->reveal()]);
        $this->subject1->getVariants()->willReturn([$this->variant1->reveal()]);
        $this->variant1->getIterator()->willReturn(new \ArrayIterator([$this->iteration1->reveal()]));

        $suite = $this->createSuite([
            $this->bench1->reveal(),
        ], [
            $this->env1->reveal(),
        ]);

        $this->assertSame([$this->iteration1->reveal()], $suite->getIterations());
        $this->assertSame([
            $this->variant1->reveal(),
        ], $suite->getVariants());
        $this->assertSame([
            $this->subject1->reveal(),
        ], $suite->getSubjects());
    }

    /**
     * It should return all of the error stacks.
     */
    public function testGetErrorStacks()
    {
        $errorStack = $this->prophesize(ErrorStack::class);
        $this->bench1->getSubjects()->willReturn([$this->subject1->reveal()]);
        $this->subject1->getVariants()->willReturn([$this->variant1->reveal()]);
        $this->variant1->hasErrorStack()->willReturn(true);
        $this->variant1->getErrorStack()->willReturn($errorStack->reveal());

        $suite = $this->createSuite([
            $this->bench1->reveal(),
        ], [
            $this->env1->reveal(),
        ]);

        $this->assertSame([
            $errorStack->reveal(),
        ], $suite->getErrorStacks());
    }

    /**
     * It should return the summary.
     */
    public function testGetSummary()
    {
        $errorStack = $this->prophesize(ErrorStack::class);
        $this->bench1->getSubjects()->willReturn([$this->subject1->reveal()]);
        $this->subject1->getVariants()->willReturn([$this->variant1->reveal()]);
        $this->variant1->hasErrorStack()->willReturn(true);
        $this->variant1->count()->willReturn(1);
        $this->variant1->getSubject()->willReturn($this->subject1->reveal());
        $this->variant1->getRevolutions()->willReturn(10);
        $this->variant1->getRejectCount()->willReturn(0);
        $this->variant1->getRejectCount()->willReturn(0);
        $this->variant1->getFailures()->willReturn(new AssertionFailures($this->variant1->reveal()));
        $this->variant1->getWarnings()->willReturn(new AssertionWarnings($this->variant1->reveal()));
        $this->variant1->getErrorStack()->willReturn($errorStack->reveal());

        $suite = $this->createSuite([
            $this->bench1->reveal(),
        ], [
            $this->env1->reveal(),
        ]);

        $summary = $suite->getSummary();
        $this->assertInstanceOf('PhpBench\Model\Summary', $summary);
    }

    public function testFindVariant(): void
    {
        $suite = $this->createSuite([]);
        $variant = $suite->createBenchmark('Foobar')->createSubject('barfoo')->createVariant(
            ParameterSet::create('one', []),
            1,
            1
        );

        self::assertSame($variant, $suite->findVariant(
            'Foobar',
            'barfoo',
            'one'
        ));
    }

    private function createSuite(array $benchmarks = [], array $informations = []): Suite
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
