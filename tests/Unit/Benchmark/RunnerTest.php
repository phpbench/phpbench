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

namespace PhpBench\Tests\Unit\Benchmark;

use Exception;
use Generator;
use InvalidArgumentException;
use PhpBench\Assertion\AssertionProcessor;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Runner;
use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Environment\Information;
use PhpBench\Environment\Supplier;
use PhpBench\Executor;
use PhpBench\Executor\Benchmark\TestExecutor;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Suite;
use PhpBench\Registry\Config;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Tests\TestCase;
use PhpBench\Tests\Util\TestUtil;
use Prophecy\Prophecy\ObjectProphecy;

class RunnerTest extends TestCase
{
    const TEST_PATH = 'path/to/bench.php';

    /**
     * @var ObjectProphecy<Suite>
     */
    private $suite;

    /**
     * @var ObjectProphecy<BenchmarkMetadata>
     */
    private $benchmark;

    /**
     * @var TestExecutor
     */
    private $executor;

    /**
     * @var ObjectProphecy<ConfigurableRegistry>
     */
    private $executorRegistry;

    /**
     * @var ObjectProphecy<AssertionProcessor>
     */
    private $assertion;

    /**
     * @var Config
     */
    private $executorConfig;

    /**
     * @var ObjectProphecy<Supplier>
     */
    private $envSupplier;

    /**
     * @var array<mixed>
     */
    private $informations;

    /**
     * @var Runner
     */
    private $runner;

    protected function setUp(): void
    {
        $this->suite = $this->prophesize(Suite::class);
        $this->benchmark = $this->prophesize(BenchmarkMetadata::class);
        $this->executorRegistry = $this->prophesize(ConfigurableRegistry::class);
        $this->executor = new TestExecutor();
        $this->assertion = $this->prophesize(AssertionProcessor::class);
        $this->executorConfig = $this->setUpExecutorConfig([]);
        $this->envSupplier = $this->prophesize(Supplier::class);
        $this->informations = [];
        $this->envSupplier->getInformations()->willReturn($this->informations);

        $this->runner = new Runner(
            $this->executorRegistry->reveal(),
            $this->envSupplier->reveal(),
            $this->assertion->reveal(),
            null,
            null
        );

        $this->executorRegistry->getService('microtime')->willReturn(
            $this->executor
        );
    }

    /**
     * @dataProvider provideRunner
     */
    public function testRunner($iterations, $revs, array $parameters, $assertionCallbacks): void
    {
        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name');
        $subject->setIterations($iterations);
        $subject->setBeforeMethods(['beforeFoo']);
        $subject->setParameterSets([[$parameters]]);
        $subject->setRevs($revs);

        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);

        $suite = $this->runner->run([ $this->benchmark->reveal() ], RunnerConfig::create()->withTag('context'));

        $this->assertInstanceOf('PhpBench\Model\Suite', $suite);
        $this->assertNoErrors($suite);

        self::assertCount((int)(count($revs) * array_sum($iterations)), $this->executor->executedSubjects);

        foreach ($assertionCallbacks as $callback) {
            $callback($this, $suite);
        }
    }

    /**
     * @return Generator<mixed>
     */
    public function provideRunner(): Generator
    {
        yield [
            [1],
            [1],
            [],
            function ($test, $suite) {
                $test->assertEquals(1, $suite->getSummary()->getNbIterations());
            },
        ];

        yield [
            [1],
            [3],
            [],
            function ($test, $suite) {
                $test->assertEquals(1, $iterations = $suite->getIterations());
                $iteration = reset($iterations);
                $test->assertEquals(3, $iteration->getVariant()->getRevolutions());
            },
        ];

        yield [
            [4],
            [3],
            [],
            function ($test, $suite) {
                $test->assertEquals(4, $iterations = $suite->getIterations());
                $iteration = reset($iterations);
                $test->assertEquals(3, $iteration->getVariant()->getRevolutions());
            },
        ];

        yield [
            [1],
            [1],
            ['one' => 'two', 'three' => 'four'],
            function ($test, $suite) {
                $test->assertEquals(1, $iterations = $suite->getIterations());
                $iteration = reset($iterations);
                $parameters = $iteration->getVariant()->getParameterSet();
                $test->assertEquals('two', $parameters['one']);
                $test->assertEquals('four', $parameters['four']);
            },
        ];

        yield [
            [1],
            [1],
            ['one', 'two'],
            function ($test, $suite) {
                $test->assertEquals(1, $iterations = $suite->getIterations());
                $iteration = reset($iterations);
                $parameters = $iteration->getVariant()->getParameterSet();
                $test->assertEquals('one', $parameters[0]);
                $test->assertEquals('two', $parameters[1]);
            },
        ];

        yield [
            [1],
            [1],
            ['one' => ['three' => 'four']],
            function ($test, $suite) {
                $test->assertEquals(1, $iterations = $suite->getIterations());
                $iteration = reset($iterations);
                $parameters = $iteration->getVariant()->getParameterSet();
                $test->assertEquals(['three', 'four'], $parameters[0]);
            },
        ];
    }

    /**
     * It should skip subjects that should be skipped.
     */
    public function testSkip(): void
    {
        $subject1 = new SubjectMetadata($this->benchmark->reveal(), 'one');
        $subject2 = new SubjectMetadata($this->benchmark->reveal(), 'two');
        $subject3 = new SubjectMetadata($this->benchmark->reveal(), 'three');

        $subject2->setSkip(true);
        $this->benchmark->getSubjects()->willReturn([
            $subject1,
            $subject2,
            $subject3,
        ]);
        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $suite = $this->runner->run([ $this->benchmark->reveal() ], RunnerConfig::create());

        $this->assertInstanceOf('PhpBench\Model\Suite', $suite);
        $this->assertNoErrors($suite);
        $this->assertEquals(2, $suite->getSummary()->getNbSubjects());
        $subjects = $suite->getSubjects();

        $this->assertEquals('one', $subjects[0]->getName());
        $this->assertEquals('three', $subjects[1]->getName());
    }

    public function testSleep(): void
    {
        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name');
        $subject->setSleep(50);
        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);
        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $suite = $this->runner->run([ $this->benchmark->reveal() ], RunnerConfig::create());
        $this->assertNoErrors($suite);
        self::assertEquals(50, $this->executor->lastSubjectOrException()->getSleep());
    }

    public function testOverrideMetadata(): void
    {
        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name');
        $subject->setSleep(50);
        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);
        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $suite = $this->runner->run([ $this->benchmark->reveal() ], RunnerConfig::create()
            ->withSleep(100)
            ->withRetryThreshold(12)
            ->withWarmup([66])
            ->withRevolutions([88]));

        $this->assertNoErrors($suite);

        self::assertEquals(100, $this->executor->lastSubjectOrException()->getSleep());
        self::assertEquals(12, $this->executor->lastSubjectOrException()->getRetryThreshold());
        self::assertEquals(66, $this->executor->lastVariantOrException()->getWarmup());
        self::assertEquals(88, $this->executor->lastVariantOrException()->getRevolutions());
    }

    /**
     * It should serialize the retry threshold.
     */
    public function testRetryThreshold(): void
    {
        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name');
        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);
        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $suite = $this->runner->run(
            [ $this->benchmark->reveal() ],
            RunnerConfig::create()->withRetryThreshold(10)
        );

        self::assertEquals(10, $this->executor->lastSubjectOrException()->getRetryThreshold());
        $this->assertInstanceOf('PhpBench\Model\Suite', $suite);
    }

    /**
     * It should call the before and after class methods.
     */
    public function testBeforeAndAfterClass(): void
    {
        TestUtil::configureBenchmarkMetadata($this->benchmark, [
            'beforeClassMethods' => ['afterClass'],
            'afterClassMethods' => ['beforeClass'],
        ]);

        $this->benchmark->getSubjects()->willReturn([]);

        $this->runner->run([ $this->benchmark->reveal() ], RunnerConfig::create());
        self::assertFalse($this->executor->healthChecked);
        self::assertContainsEquals('afterClass', $this->executor->executedMethods);
        self::assertContainsEquals('beforeClass', $this->executor->executedMethods);
    }

    /**
     * It should handle exceptions thrown by the executor.
     * It should handle nested exceptions.
     */
    public function testHandleExceptions(): void
    {
        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name');
        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);

        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $this->setUpExecutorConfig([
            'exception' => new Exception('Foobar', 0, new InvalidArgumentException('Barfoo'))
        ]);

        $suite = $this->runner->run([ $this->benchmark->reveal() ], RunnerConfig::create());

        $errorStacks = $suite->getErrorStacks();
        $this->assertCount(1, $errorStacks);
        $errorStack = iterator_to_array(reset($errorStacks));
        $this->assertCount(2, $errorStack);
        $this->assertEquals('Exception', $errorStack[0]->getClass());
        $this->assertEquals('InvalidArgumentException', $errorStack[1]->getClass());
        $this->assertEquals('Foobar', $errorStack[0]->getMessage());
        $this->assertEquals('Barfoo', $errorStack[1]->getMessage());
    }

    /**
     * It should add environmental information to the DOM.
     */
    public function testEnvironment(): void
    {
        $informations = [
            'hello' => new Information('hello', ['say' => 'goodbye'])
        ];
        $this->envSupplier->getInformations()->willReturn($informations);

        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name');
        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);

        TestUtil::configureBenchmarkMetadata($this->benchmark);
        $suite = $this->runner->run([ $this->benchmark->reveal() ], RunnerConfig::create());
        $envInformations = $suite->getEnvInformations();
        $this->assertSame((array) $informations, (array) $envInformations);
    }

    private function assertNoErrors(Suite $suite): void
    {
        if ($errorStacks = $suite->getErrorStacks()) {
            $errorStack = reset($errorStacks);
            $this->fail('Runner encountered error: ' . $errorStack->getTop()->getMessage());
        }

        if ($errorStacks = $suite->getErrorStacks()) {
            $errorStack = reset($errorStacks);
            $this->fail('Runner encountered error: ' . $errorStack->getTop()->getMessage());
        }
    }

    private function exampleResults(): ExecutionResults
    {
        return ExecutionResults::fromResults(
            new TimeResult(10),
            new MemoryResult(10, 10, 10)
        );
    }

    /**
     * @param array<string, mixed>
     */
    private function setUpExecutorConfig(array $config = []): void
    {
        $this->executorConfig = new Config('microtime', array_merge([
            'exception' => null,
            'executor' => 'microtime',
            'results' => [TimeResult::fromArray(['net' => 1])]
        ], $config));
        $this->executorRegistry->getConfig('microtime')->willReturn(
            $this->executorConfig
        );
    }
}
