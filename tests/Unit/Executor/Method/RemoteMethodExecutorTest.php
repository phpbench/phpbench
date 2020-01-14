<?php

namespace PhpBench\Tests\Unit\Executor\Method;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Executor\Method\RemoteMethodExecutor;
use PhpBench\Tests\PhpBenchTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class RemoteMethodExecutorTest extends PhpBenchTestCase
{
    /**
     * @var RemoteMethodExecutor
     */
    private $executor;

    /**
     * @var ObjectProphecy
     */
    private $benchmarkMetadata;

    protected function setUp(): void
    {
        $this->staticMethodFile = __DIR__ . '/../benchmarks/static_method.tmp';
        $this->executor = new RemoteMethodExecutor(new Launcher());
        $this->benchmarkMetadata = $this->prophesize(BenchmarkMetadata::class);
        $this->benchmarkMetadata->getPath()->willReturn(__DIR__ . '/../benchmarks/MicrotimeExecutorBench.php');
        $this->benchmarkMetadata->getClass()->willReturn('PhpBench\Tests\Unit\Executor\benchmarks\MicrotimeExecutorBench');
    }

    /**
     * It should execute arbitrary methods on the benchmark class.
     */
    public function testExecuteMethods()
    {
        $this->executor->executeMethods($this->benchmarkMetadata->reveal(), ['initDatabase']);
        $this->assertFileExists($this->workspacePath('static_method.tmp'));
    }
}
