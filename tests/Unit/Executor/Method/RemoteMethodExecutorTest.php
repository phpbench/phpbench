<?php

namespace PhpBench\Tests\Unit\Executor\Method;

use PhpBench\Tests\Unit\Executor\benchmarks\MicrotimeExecutorBench;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Executor\Method\RemoteMethodExecutor;
use PhpBench\Executor\MethodExecutorContext;
use PhpBench\Remote\Launcher;
use PhpBench\Tests\PhpBenchTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class RemoteMethodExecutorTest extends PhpBenchTestCase
{
    private RemoteMethodExecutor $executor;

    /**
     * @var ObjectProphecy<BenchmarkMetadata>
     */
    private ObjectProphecy $benchmarkMetadata;

    protected function setUp(): void
    {
        $this->executor = new RemoteMethodExecutor(new Launcher());
        $this->benchmarkMetadata = $this->prophesize(BenchmarkMetadata::class);
        $this->benchmarkMetadata->getPath()->willReturn(__DIR__ . '/../benchmarks/MicrotimeExecutorBench.php');
        $this->benchmarkMetadata->getClass()->willReturn(MicrotimeExecutorBench::class);
    }

    /**
     * It should execute arbitrary methods on the benchmark class.
     */
    public function testExecuteMethods(): void
    {
        $this->executor->executeMethods(
            MethodExecutorContext::fromBenchmarkMetadata($this->benchmarkMetadata->reveal()),
            ['initDatabase']
        );
        $this->assertFileExists($this->workspacePath('static_method.tmp'));
    }
}
