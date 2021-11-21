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

use PhpBench\Model\Benchmark;
use PhpBench\Model\Error;
use PhpBench\Model\ErrorStack;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Suite;
use PhpBench\Tests\TestCase;
use PhpBench\Tests\Util\SuiteBuilder;

class SuiteTest extends TestCase
{
    /**
     * It should add a benchmark.
     */
    public function testCreateBenchmark(): void
    {
        $suite = SuiteBuilder::create('foo')->build();
        $benchmark = $suite->createBenchmark('FooBench');
        $this->assertInstanceOf('PhpBench\Model\Benchmark', $benchmark);
    }

    /**
     * It should return all of the iterations in the suite.
     * It should return all of the subjects
     * It should return all of the variants.
     */
    public function testGetIterations(): void
    {
        $suite = SuiteBuilder::create('suite1')
            ->benchmark('one')
                ->subject('one')
                    ->variant('1')
                        ->iteration()->setResult(new TimeResult(1, 1))->end()
                        ->iteration()->setResult(new TimeResult(2, 2))->end()
                    ->end()
                    ->variant('2')
                        ->iteration()->setResult(new TimeResult(10, 1))->end()
                    ->end()
                ->end()
            ->end()
            ->benchmark('two')
                ->subject('one')
                    ->variant('1')->iteration()->setResult(new TimeResult(10, 1))->end()->end()
                    ->variant('2')->iteration()->setResult(new TimeResult(10, 1))->end()->end()
                ->end()
            ->end()
            ->build();


        self::assertCount(5, $suite->getIterations());
    }

    /**
     * It should return all of the error stacks.
     */
    public function testGetErrorStacks(): void
    {
        $suite = SuiteBuilder::create('suite1')
            ->benchmark('one')
                ->subject('one')
                    ->variant('1')
                        ->withError(Error::fromException(new \Exception('Hello')))
                    ->end()
                ->end()
            ->end()
            ->build();

        $stacks = $suite->getErrorStacks();
        self::assertCount(1, $stacks);
        $stack = reset($stacks);
        assert($stack instanceof ErrorStack);
        self::assertEquals('Hello', $stack->getTop()->getMessage());
    }

    /**
     * It should return the summary.
     */
    public function testGetSummary(): void
    {
        $suite = SuiteBuilder::create('suite1')
            ->benchmark('one')
                ->subject('one')
                    ->variant('1')->iteration()->setResult(new TimeResult(10, 1))->end()->end()
                    ->variant('2')->iteration()->setResult(new TimeResult(10, 1))->end()->end()
                ->end()
            ->end()
            ->benchmark('two')
                ->subject('one')
                    ->variant('1')->iteration()->setResult(new TimeResult(10, 1))->end()->end()
                    ->variant('2')->iteration()->setResult(new TimeResult(10, 1))->end()->end()
                ->end()
            ->end()
            ->build();


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

        self::assertSame($variant, $suite->findVariantByParameterSetName(
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
