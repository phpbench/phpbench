<?php

namespace PhpBench\Tests\Unit\Executor\Method;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Executor\Method\RemoteMethodExecutor;
use PhpBench\Executor\MethodExecutorContext;
use PhpBench\Remote\Launcher;
use PhpBench\Tests\PhpBenchTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class RemoteMethodExecutorTest extends PhpBenchTestCase
{
    /**
     * @var RemoteMethodExecutor
     */
    private $executor;

    /**
     * @var ObjectProphecy<BenchmarkMetadata>
     */
    private $benchmarkMetadata;

    /**
     * @var string
     */
    private $staticMethodFile;

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
    public function testExecuteMethods(): void
    {
        $this->executor->executeMethods(
            MethodExecutorContext::fromBenchmarkMetadata($this->benchmarkMetadata->reveal()),
            ['initDatabase']
        );
        $this->assertFileExists($this->workspacePath('static_method.tmp'));
    }
}
