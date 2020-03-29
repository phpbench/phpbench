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
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Executor\Benchmark\MicrotimeExecutor;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PhpBench\Tests\PhpBenchTestCase;
use Prophecy\Argument;
use RuntimeException;

class MicrotimeExecutorTest extends PhpBenchTestCase
{
    protected function setUp(): void
    {
        $this->initWorkspace();

        $this->metadata = $this->prophesize(SubjectMetadata::class);
        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->benchmarkMetadata = $this->prophesize(BenchmarkMetadata::class);
        $this->variant = $this->prophesize(Variant::class);

        $launcher = new Launcher(null, null);
        $this->executor = new MicrotimeExecutor($launcher);

        $this->benchmarkMetadata->getPath()->willReturn(__DIR__ . '/../benchmarks/MicrotimeExecutorBench.php');
        $this->benchmarkMetadata->getClass()->willReturn('PhpBench\Tests\Unit\Executor\benchmarks\MicrotimeExecutorBench');
        $this->iteration = $this->prophesize(Iteration::class);
        $this->metadata->getBenchmark()->willReturn($this->benchmarkMetadata->reveal());
        $this->iteration->getVariant()->willReturn($this->variant->reveal());
    }

    /**
     * It should create a script which benchmarks the code and returns
     * the time taken and the memory used.
     */
    public function testExecute()
    {
        $this->metadata->getTimeout()->willReturn(0);
        $this->metadata->getBeforeMethods()->willReturn([]);
        $this->metadata->getAfterMethods()->willReturn([]);
        $this->metadata->getName()->willReturn('doSomething');
        $this->variant->getParameterSet()->willReturn(new ParameterSet('one'));
        $this->variant->getRevolutions()->willReturn(10);
        $this->variant->getWarmup()->willReturn(1);

        $this->iteration->setResult(Argument::type(TimeResult::class))->shouldBeCalled();
        $this->iteration->setResult(Argument::type(MemoryResult::class))->shouldBeCalled();

        $this->executor->execute(
            $this->metadata->reveal(),
            $this->iteration->reveal(),
            new Config('test', [])
        );

        $this->assertFileNotExists($this->workspacePath('before_method.tmp'));
        $this->assertFileNotExists($this->workspacePath('after_method.tmp'));
        $this->assertFileExists($this->workspacePath('revs.tmp'));

        // 10 revolutions + 1 warmup
        $this->assertStringEqualsFile($this->workspacePath('revs.tmp'), '11');
    }

    /**
     * It should prevent output from the benchmarking class.
     *
     */
    public function testRepressOutput()
    {
        $this->expectExceptionMessage('Benchmark made some noise');
        $this->expectException(RuntimeException::class);
        $this->metadata->getBeforeMethods()->willReturn([]);
        $this->metadata->getAfterMethods()->willReturn([]);
        $this->metadata->getName()->willReturn('benchOutput');
        $this->metadata->getRevs()->willReturn(10);
        $this->metadata->getTimeout()->willReturn(0);
        $this->metadata->getWarmup()->willReturn(0);
        $this->variant->getParameterSet()->willReturn(new ParameterSet('one'));
        $this->variant->getRevolutions()->willReturn(10);
        $this->variant->getWarmup()->willReturn(0);

        $results = $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', []));

        $this->assertInstanceOf('PhpBench\Model\ResultCollection', $results);
    }

    /**
     * It should execute methods before the benchmark subject.
     */
    public function testExecuteBefore()
    {
        $this->metadata->getBeforeMethods()->willReturn(['beforeMethod']);
        $this->metadata->getAfterMethods()->willReturn([]);
        $this->metadata->getName()->willReturn('doSomething');
        $this->metadata->getTimeout()->willReturn(0);
        $this->variant->getParameterSet()->willReturn(new ParameterSet('one'));
        $this->variant->getRevolutions()->willReturn(1);
        $this->variant->getWarmup()->willReturn(0);

        $this->iteration->setResult(Argument::type(TimeResult::class))->shouldBeCalled();
        $this->iteration->setResult(Argument::type(MemoryResult::class))->shouldBeCalled();

        $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', []));

        $this->assertFileExists($this->workspacePath('before_method.tmp'));
    }

    /**
     * It should execute methods after the benchmark subject.
     */
    public function testExecuteAfter()
    {
        $this->metadata->getBeforeMethods()->willReturn([]);
        $this->metadata->getAfterMethods()->willReturn(['afterMethod']);
        $this->metadata->getName()->willReturn('doSomething');
        $this->metadata->getTimeout()->willReturn(0);
        $this->variant->getParameterSet()->willReturn(new ParameterSet('one'));
        $this->variant->getRevolutions()->willReturn(1);
        $this->variant->getWarmup()->willReturn(0);

        $this->iteration->setResult(Argument::type(TimeResult::class))->shouldBeCalled();
        $this->iteration->setResult(Argument::type(MemoryResult::class))->shouldBeCalled();

        $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', []));

        $this->assertFileExists($this->workspacePath('after_method.tmp'));
    }

    /**
     * It should pass parameters to the benchmark method.
     */
    public function testParameters()
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

        $this->iteration->setResult(Argument::type(TimeResult::class))->shouldBeCalled();
        $this->iteration->setResult(Argument::type(MemoryResult::class))->shouldBeCalled();

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
    public function testParametersBeforeSubject()
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

        $this->iteration->setResult(Argument::type(TimeResult::class))->shouldBeCalled();
        $this->iteration->setResult(Argument::type(MemoryResult::class))->shouldBeCalled();

        $this->executor->execute($this->metadata->reveal(), $this->iteration->reveal(), new Config('test', []));

        $this->assertFileExists($this->workspacePath('parambefore.tmp'));
        $params = json_decode(file_get_contents($this->workspacePath('parambefore.tmp')), true);
        $this->assertEquals($expected->getArrayCopy(), $params);

        $this->assertFileExists($this->workspacePath('paramafter.tmp'));
        $params = json_decode(file_get_contents($this->workspacePath('paramafter.tmp')), true);
        $this->assertEquals($expected->getArrayCopy(), $params);
    }
}
