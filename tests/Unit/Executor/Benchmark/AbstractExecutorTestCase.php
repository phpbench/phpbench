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

namespace PhpBench\Tests\Unit\Executor\Benchmark;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\Exception\ExecutionError;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PhpBench\Tests\PhpBenchTestCase;

abstract class AbstractExecutorTestCase extends PhpBenchTestCase
{
    /**
     * @var ObjectProphecy
     */
    protected $metadata;
    /**
     * @var ObjectProphecy
     */
    protected $benchmark;
    /**
     * @var ObjectProphecy
     */
    protected $benchmarkMetadata;
    /**
     * @var ObjectProphecy
     */
    protected $variant;
    /**
     * @var MicrotimeExecutor
     */
    protected $executor;
    /**
     * @var ObjectProphecy
     */
    protected $iteration;

    protected function setUp(): void
    {
        $this->initWorkspace();

        $this->metadata = $this->prophesize(SubjectMetadata::class);
        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->benchmarkMetadata = $this->prophesize(BenchmarkMetadata::class);
        $this->variant = $this->prophesize(Variant::class);

        $this->executor = $this->createExecutor();

        $this->benchmarkMetadata->getPath()->willReturn(__DIR__ . '/../benchmarks/MicrotimeExecutorBench.php');
        $this->benchmarkMetadata->getClass()->willReturn('PhpBench\Tests\Unit\Executor\benchmarks\MicrotimeExecutorBench');
        $this->iteration = $this->prophesize(Iteration::class);
        $this->metadata->getBenchmark()->willReturn($this->benchmarkMetadata->reveal());
        $this->iteration->getVariant()->willReturn($this->variant->reveal());
        $this->metadata->getTimeout()->willReturn(0);
        $this->metadata->getBeforeMethods()->willReturn([]);
        $this->metadata->getAfterMethods()->willReturn([]);
        $this->metadata->getName()->willReturn('doSomething');
        $this->variant->getParameterSet()->willReturn(new ParameterSet('one'));
        $this->variant->getRevolutions()->willReturn(10);
        $this->variant->getWarmup()->willReturn(1);
    }

    abstract protected function createExecutor(): BenchmarkExecutorInterface;

    abstract protected function assertExecute(ExecutionResults $results): void;

    /**
     * It should create a script which benchmarks the code and returns
     * the time taken and the memory used.
     */
    public function testExecute(): void
    {
        $results = $this->executor->execute(
            $this->metadata->reveal(),
            $this->iteration->reveal(),
            new Config('test', [])
        );

        $this->assertExecute($results);
    }

    public function testExceptionWhenClassNotFound(): void
    {
        $this->expectException(ExecutionError::class);
        $this->benchmarkMetadata->getClass()->willReturn('Foobar');

        $this->executor->execute(
            $this->metadata->reveal(),
            $this->iteration->reveal(),
            new Config('test', [])
        );
    }

    /**
     * It should execute methods before the benchmark subject.
     */
    public function testExecuteBefore(): void
    {
        $this->metadata->getBeforeMethods()->willReturn(['beforeMethod']);
        $this->metadata->getAfterMethods()->willReturn([]);
        $this->metadata->getName()->willReturn('doSomething');
        $this->metadata->getTimeout()->willReturn(0);
        $this->variant->getParameterSet()->willReturn(new ParameterSet('one'));
        $this->variant->getRevolutions()->willReturn(1);
        $this->variant->getWarmup()->willReturn(0);

        $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', []));

        $this->assertFileExists($this->workspacePath('before_method.tmp'));
    }

    /**
     * It should execute methods after the benchmark subject.
     */
    public function testExecuteAfter(): void
    {
        $this->metadata->getBeforeMethods()->willReturn([]);
        $this->metadata->getAfterMethods()->willReturn(['afterMethod']);
        $this->metadata->getName()->willReturn('doSomething');
        $this->metadata->getTimeout()->willReturn(0);
        $this->variant->getParameterSet()->willReturn(new ParameterSet('one'));
        $this->variant->getRevolutions()->willReturn(1);
        $this->variant->getWarmup()->willReturn(0);

        $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', []));

        $this->assertFileExists($this->workspacePath('after_method.tmp'));
    }

    /**
     * It should pass parameters to the benchmark method.
     */
    public function testParameters(): void
    {
        $this->metadata->getBeforeMethods()->willReturn([]);
        $this->metadata->getAfterMethods()->willReturn([]);
        $this->metadata->getName()->willReturn('parameterized');
        $this->metadata->getTimeout()->willReturn(0);

        $this->variant->getParameterSet()->willReturn(new ParameterSet(0, [
            'one' => 'two',
            'three' => 'four',
        ]));
        $this->variant->getRevolutions()->willReturn(1);
        $this->variant->getWarmup()->willReturn(0);

        $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', []));
        $this->assertFileExists($this->workspacePath('param.tmp'));
        $params = json_decode(file_get_contents($this->workspacePath('param.tmp')), true);
        $this->assertEquals([
            'one' => 'two',
            'three' => 'four',
        ], $params);
    }

    /**
     * It should pass parameters to the before metadata and after metadata methods.
     */
    public function testParametersBeforeSubject(): void
    {
        $expected = new ParameterSet(0, [
            'one' => 'two',
            'three' => 'four',
        ]);

        $this->metadata->getTimeout()->willReturn(0);
        $this->metadata->getBeforeMethods()->willReturn(['parameterizedBefore']);
        $this->metadata->getAfterMethods()->willReturn(['parameterizedAfter']);
        $this->metadata->getName()->willReturn('parameterized');
        $this->variant->getParameterSet()->willReturn($expected);
        $this->variant->getRevolutions()->willReturn(1);
        $this->variant->getWarmup()->willReturn(0);

        $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', []));

        $this->assertFileExists($this->workspacePath('parambefore.tmp'));
        $params = json_decode(file_get_contents($this->workspacePath('parambefore.tmp')), true);
        $this->assertEquals($expected->getArrayCopy(), $params);

        $this->assertFileExists($this->workspacePath('paramafter.tmp'));
        $params = json_decode(file_get_contents($this->workspacePath('paramafter.tmp')), true);
        $this->assertEquals($expected->getArrayCopy(), $params);
    }
}
