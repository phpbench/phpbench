<?php

namespace PhpBench\Tests\Unit\Benchmark\Executor\Method;

use PHPUnit\Framework\TestCase;
use PhpBench\Executor\Method\RemoteMethodExecutor;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Remote\Launcher;

class RemoteMethodExecutorTest extends TestCase
{
    /**
     * @var string
     */
    private $staticMethodFile;

    /**
     * @var RemoteMethodExecutor
     */
    private $executor;

    protected function setUp()
    {
        $this->staticMethodFile = __DIR__ . '/../benchmarks/static_method.tmp';
        $this->executor = new RemoteMethodExecutor(new Launcher());
        $this->benchmarkMetadata = $this->prophesize(BenchmarkMetadata::class);
        $this->benchmarkMetadata->getPath()->willReturn(__DIR__ . '/../benchmarks/MicrotimeExecutorBench.php');
        $this->benchmarkMetadata->getClass()->willReturn('PhpBench\Tests\Unit\Benchmark\Executor\benchmarks\MicrotimeExecutorBench');
    }

    /**
     * It should execute arbitrary methods on the benchmark class.
     */
    public function testExecuteMethods()
    {
        $this->executor->executeMethods($this->benchmarkMetadata->reveal(), ['initDatabase']);
        $this->assertFileExists($this->staticMethodFile);
    }
}
