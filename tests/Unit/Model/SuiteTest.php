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

use PhpBench\Assertion\VariantAssertionResults;
use PhpBench\Environment\Information;
use PhpBench\Model\Benchmark;
use PhpBench\Model\ErrorStack;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\Tests\TestCase;
use PhpBench\Tests\Util\SuiteBuilder;

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
    public function testCreateBenchmark(): void
    {
        $benchmark = $this->createSuite([])->createBenchmark('FooBench');
        $this->assertInstanceOf('PhpBench\Model\Benchmark', $benchmark);
    }

    /**
     * It should return all of the iterations in the suite.
     * It should return all of the subjects
     * It should return all of the variants.
     */
    public function testGetIterations(): void
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
    public function testGetErrorStacks(): void
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
    public function testGetSummary(): void
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
        $this->variant1->getAssertionResults()->willReturn(new VariantAssertionResults($this->variant1->reveal(), []));
        $this->variant1->getErrorStack()->willReturn($errorStack->reveal());
        $errorStack->count()->willReturn(0);

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
            ParameterSet::fromSerializedParameters('one', []),
            1,
            1
        );

        self::assertSame($variant, $suite->findVariant(
            'Foobar',
            'barfoo',
            'one'
        ));
    }

    public function testFilterBySubjectNames(): void
    {
        $suite = SuiteBuilder::create('test')
            ->benchmark('Foobar')
                ->subject('subject_one')->end()
                ->subject('subject_two')->end()
            ->end()
            ->build();

        self::assertCount(2, $suite->getSubjects());
        $suite = $suite->filter(['subject_one'], []);
        self::assertCount(1, $suite->getSubjects());
    }

    public function testFilterByBenchmarkNames(): void
    {
        $suite = SuiteBuilder::create('test')
            ->benchmark('Foobar')
                ->subject('subject_one')->end()
                ->subject('subject_two')->end()
            ->end()
            ->benchmark('Bazboo')
                ->subject('subject_one')->end()
                ->subject('subject_two')->end()
            ->end()
            ->build();

        self::assertCount(4, $suite->getSubjects(), 'Pre filter');
        $suite = $suite->filter(['Foobar'], []);
        self::assertCount(2, $suite->getSubjects(), 'Post filter');
    }

    public function testFilterByVariants(): void
    {
        $suite = SuiteBuilder::create('test')
            ->benchmark('Foobar')
                ->subject('subject_one')
                    ->variant('variant one')->end()
                    ->variant('variant two')->end()
                ->end()
                ->subject('subject_two')
                    ->variant('variant one')->end()
                    ->variant('variant two')->end()
                ->end()
            ->end()
            ->benchmark('Bazboo')
                ->subject('subject_one')
                    ->variant('variant one')->end()
                    ->variant('variant two')->end()
                ->end()
                ->subject('subject_two')
                    ->variant('variant one')->end()
                    ->variant('variant two')->end()
                ->end()
            ->end()
            ->build();

        self::assertCount(8, $suite->getVariants(), 'Pre filter');
        $suite = $suite->filter(['Bazboo'], ['variant one']);
        self::assertCount(2, $suite->getVariants(), 'Post filter');
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
