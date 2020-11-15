<?php

namespace PhpBench\Executor;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;

final class MethodExecutorContext
{
    /**
     * @var string
     */
    private $benchmarkPath;

    /**
     * @var string
     */
    private $benchmarkClass;

    public function __construct(
        string $benchmarkPath,
        string $benchmarkClass
    ) {
        $this->benchmarkPath = $benchmarkPath;
        $this->benchmarkClass = $benchmarkClass;
    }

    public static function fromBenchmarkMetadata(BenchmarkMetadata $benchmarkMetadata): self
    {
        return new self($benchmarkMetadata->getPath(), $benchmarkMetadata->getClass());
    }

    public function getBenchmarkClass(): string
    {
        return $this->benchmarkClass;
    }

    public function getBenchmarkPath(): string
    {
        return $this->benchmarkPath;
    }
}
