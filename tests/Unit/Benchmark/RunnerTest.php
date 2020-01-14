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

use PhpBench\Assertion\AssertionProcessor;
use PhpBench\Benchmark\BenchmarkFinder;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Runner;
use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Environment\Information;
use PhpBench\Environment\Supplier;
use PhpBench\Executor;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\HealthCheckInterface;
use PhpBench\Executor\MethodExecutorInterface;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Suite;
use PhpBench\Registry\Config;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Tests\Util\TestUtil;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class RunnerTest extends TestCase
{
    const TEST_PATH = 'path/to/bench.php';

    /**
     * @var ObjectProphecy
     */
    private $benchmarkFinder;

    /**
     * @var ObjectProphecy
     */
    private $suite;

    /**
     * @var ObjectProphecy
     */
    private $benchmark;

    /**
     * @var ObjectProphecy
     */
    private $executor;

    /**
     * @var ObjectProphecy
     */
    private $executorRegistry;

    /**
     * @var ObjectProphecy
     */
    private $assertion;

    /**
     * @var Config
     */
    private $executorConfig;

    /**
     * @var ObjectProphecy
     */
    private $envSupplier;

    /**
     * @var ArrayObject
     */
    private $informations;

    /**
     * @var Runner
     */
    private $runner;

    protected function setUp(): void
    {
        $this->benchmarkFinder = $this->prophesize(BenchmarkFinder::class);
        $this->suite = $this->prophesize(Suite::class);
        $this->benchmark = $this->prophesize(BenchmarkMetadata::class);
        $this->benchmarkFinder->findBenchmarks(self::TEST_PATH, [], [])->willReturn([
            $this->benchmark->reveal(),
        ]);
        $this->executor = $this->prophesize(BenchmarkExecutorInterface::class);
        $this->executor->willImplement(MethodExecutorInterface::class);
        $this->executor->willImplement(HealthCheckInterface::class);
        $this->executor->healthCheck()->shouldBeCalled();
        $this->executorRegistry = $this->prophesize(ConfigurableRegistry::class);
        $this->assertion = $this->prophesize(AssertionProcessor::class);
        $this->executorConfig = new Config('test', ['executor' => 'microtime']);
        $this->envSupplier = $this->prophesize(Supplier::class);
        $this->informations = new \ArrayObject();
        $this->envSupplier->getInformations()->willReturn($this->informations);

        $this->runner = new Runner(
            $this->benchmarkFinder->reveal(),
            $this->executorRegistry->reveal(),
            $this->envSupplier->reveal(),
            $this->assertion->reveal(),
            null,
            null
        );

        $this->executorRegistry->getService('microtime')->willReturn(
            $this->executor->reveal()
        );
        $this->executorRegistry->getConfig('microtime')->willReturn(
            $this->executorConfig
        );
    }

    /**
     * It should run the tests.
     *
     * - With 1 iteration, 1 revolution
     * - With 1 iteration, 4 revolutions
     *
     * @dataProvider provideRunner
     */
    public function testRunner($iterations, $revs, array $parameters, $assertionCallbacks)
    {
        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name', 0);
        $subject->setIterations($iterations);
        $subject->setBeforeMethods(['beforeFoo']);
        $subject->setParameterSets([[$parameters]]);
        $subject->setRevs($revs);

        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);

        $this->executor->execute(
            Argument::type('PhpBench\Benchmark\Metadata\SubjectMetadata'),
            Argument::type('PhpBench\Model\Iteration'),
            new Config('test', [
                'executor' => 'microtime',
            ])
        )
        ->shouldBeCalledTimes(count($revs) * array_sum($iterations))
        ->will($this->loadIterationResultCallback());

        $suite = $this->runner->run(self::TEST_PATH, RunnerConfig::create()->withTag('context'));

        $this->assertInstanceOf('PhpBench\Model\Suite', $suite);
        $this->assertNoErrors($suite);

        foreach ($assertionCallbacks as $callback) {
            $callback($this, $suite);
        }
    }

    public function provideRunner()
    {
        return [
            [
                [1],
                [1],
                [],
                function ($test, $suite) {
                    $test->assertEquals(1, $suite->getSummary()->getNbIterations());
                },
            ],
            [
                [1],
                [3],
                [],
                function ($test, $suite) {
                    $test->assertEquals(1, $iterations = $suite->getIterations());
                    $iteration = reset($iterations);
                    $test->assertEquals(3, $iteration->getVariant()->getRevolutions());
                },
            ],
            [
                [4],
                [3],
                [],
                function ($test, $suite) {
                    $test->assertEquals(4, $iterations = $suite->getIterations());
                    $iteration = reset($iterations);
                    $test->assertEquals(3, $iteration->getVariant()->getRevolutions());
                },
            ],
            [
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
            ],
            [
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
            ],
            [
                [1],
                [1],
                ['one' => ['three' => 'four']],
                function ($test, $suite) {
                    $test->assertEquals(1, $iterations = $suite->getIterations());
                    $iteration = reset($iterations);
                    $parameters = $iteration->getVariant()->getParameterSet();
                    $test->assertEquals(['three', 'four'], $parameters[0]);
                },
            ],
        ];
    }

    /**
     * It should skip subjects that should be skipped.
     */
    public function testSkip()
    {
        $subject1 = new SubjectMetadata($this->benchmark->reveal(), 'one', 0);
        $subject2 = new SubjectMetadata($this->benchmark->reveal(), 'two', 0);
        $subject3 = new SubjectMetadata($this->benchmark->reveal(), 'three', 0);

        $subject2->setSkip(true);
        $this->benchmark->getSubjects()->willReturn([
            $subject1,
            $subject2,
            $subject3,
        ]);
        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $this->executor->execute($subject1, Argument::cetera())->will($this->loadIterationResultCallback());
        $this->executor->execute($subject2, Argument::cetera())->will($this->loadIterationResultCallback());
        $this->executor->execute($subject3, Argument::cetera())->will($this->loadIterationResultCallback());

        $suite = $this->runner->run(self::TEST_PATH, RunnerConfig::create());

        $this->assertInstanceOf('PhpBench\Model\Suite', $suite);
        $this->assertNoErrors($suite);
        $this->assertEquals(2, $suite->getSummary()->getNbSubjects());
        $subjects = $suite->getSubjects();

        $this->assertEquals('one', $subjects[0]->getName());
        $this->assertEquals('three', $subjects[1]->getName());
        $this->executor->execute($subject2, Argument::cetera())->shouldNotHaveBeenCalled();
    }

    /**
     * It should set the sleep attribute in the DOM.
     */
    public function testSleep()
    {
        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name', 0);
        $subject->setSleep(50);
        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);
        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $test = $this;
        $this->executor->execute(Argument::type('PhpBench\Benchmark\Metadata\SubjectMetadata'), Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->will(function ($args) use ($test) {
                $iteration = $args[1];
                $test->assertEquals(50, $iteration->getVariant()->getSubject()->getSleep());
                $callback = $test->loadIterationResultCallback();
                $callback($args);
            });

        $suite = $this->runner->run(self::TEST_PATH, RunnerConfig::create());
        $this->assertNoErrors($suite);
    }

    /**
     * It should allow the sleep value to be overridden.
     * It should allow the warmup value to be overridden.
     * It should allow the revs value to be overridden.
     * It should allow the retry threshold value to be overridden.
     */
    public function testOverrideThings()
    {
        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name', 0);
        $subject->setSleep(50);
        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);
        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $test = $this;
        $this->executor->execute(Argument::type('PhpBench\Benchmark\Metadata\SubjectMetadata'), Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->will(function ($args) use ($test) {
                $iteration = $args[1];
                $test->assertEquals(100, $iteration->getVariant()->getSubject()->getSleep());
                $test->assertEquals(12, $iteration->getVariant()->getSubject()->getRetryThreshold());
                $test->assertEquals(66, $iteration->getVariant()->getWarmup());
                $test->assertEquals(88, $iteration->getVariant()->getRevolutions());

                $callback = $test->loadIterationResultCallback();
                $callback($args);
            });

        $suite = $this->runner->run(self::TEST_PATH, RunnerConfig::create()
            ->withSleep(100)
            ->withRetryThreshold(12)
            ->withWarmup([66])
            ->withRevolutions([88]));
        $this->assertNoErrors($suite);
    }

    /**
     * It should set the warmup attribute in the DOM.
     */
    public function testWarmup()
    {
        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name', 0);
        $subject->setWarmup([50]);
        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);
        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $test = $this;
        $this->executor->execute(Argument::type('PhpBench\Benchmark\Metadata\SubjectMetadata'), Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->will(function ($args) use ($test) {
                $iteration = $args[1];
                $test->assertEquals(50, $iteration->getVariant()->getWarmup());

                $callback = $test->loadIterationResultCallback();
                $callback($args);
            });

        $suite = $this->runner->run(self::TEST_PATH, RunnerConfig::create());

        $this->assertInstanceOf('PhpBench\Model\Suite', $suite);
        $this->assertNoErrors($suite);
    }

    /**
     * It should serialize the retry threshold.
     */
    public function testRetryThreshold()
    {
        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name', 0);
        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);
        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $test = $this;
        $this->executor->execute(Argument::type('PhpBench\Benchmark\Metadata\SubjectMetadata'), Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->will(function ($args) use ($test) {
                $iteration = $args[1];
                $test->assertEquals(10, $iteration->getVariant()->getSubject()->getRetryThreshold());

                $callback = $test->loadIterationResultCallback();
                $callback($args);
            });

        $suite = $this->runner->run(
            self::TEST_PATH,
            RunnerConfig::create()->withRetryThreshold(10)
        );

        $this->assertInstanceOf('PhpBench\Model\Suite', $suite);
    }

    /**
     * It should call the before and after class methods.
     */
    public function testBeforeAndAfterClass()
    {
        TestUtil::configureBenchmarkMetadata($this->benchmark, [
            'beforeClassMethods' => ['afterClass'],
            'afterClassMethods' => ['beforeClass'],
        ]);

        $this->executor->executeMethods($this->benchmark->reveal(), ['beforeClass'])->shouldBeCalled();
        $this->executor->executeMethods($this->benchmark->reveal(), ['afterClass'])->shouldBeCalled();
        $this->executor->healthCheck()->shouldNotBeCalled();
        $this->benchmark->getSubjects()->willReturn([]);

        $this->runner->run(self::TEST_PATH, RunnerConfig::create());
    }

    /**
     * It should handle exceptions thrown by the executor.
     * It should handle nested exceptions.
     */
    public function testHandleExceptions()
    {
        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name', 0);
        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);
        TestUtil::configureBenchmarkMetadata($this->benchmark);

        $this->executor->execute(Argument::type('PhpBench\Benchmark\Metadata\SubjectMetadata'), Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->shouldBeCalledTimes(1)
            ->willThrow(new \Exception('Foobar', null, new \InvalidArgumentException('Barfoo')));

        $suite = $this->runner->run(self::TEST_PATH, RunnerConfig::create());

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
    public function testEnvironment()
    {
        $this->informations['hello'] = new Information('hello', ['say' => 'goodbye']);

        $subject = new SubjectMetadata($this->benchmark->reveal(), 'name', 0);
        $this->benchmark->getSubjects()->willReturn([
            $subject,
        ]);

        TestUtil::configureBenchmarkMetadata($this->benchmark);
        $this->executor->execute(Argument::type('PhpBench\Benchmark\Metadata\SubjectMetadata'), Argument::type('PhpBench\Model\Iteration'), $this->executorConfig)
            ->shouldBeCalledTimes(1)
            ->will($this->loadIterationResultCallback());
        $suite = $this->runner->run(self::TEST_PATH, RunnerConfig::create());
        $envInformations = $suite->getEnvInformations();
        $this->assertSame((array) $this->informations, (array) $envInformations);
    }

    private function assertNoErrors(Suite $suite)
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

    private function loadIterationResultCallback(array $times = ['10'])
    {
        return function ($args) {
            $args[1]->setResult(new TimeResult(10));
            $args[1]->setResult(new MemoryResult(10, 10, 10));
        };
    }
}

class RunnerTestBenchCase
{
}
