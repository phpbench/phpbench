<?php

namespace PhpBench\Executor;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;

final class MethodExecutorContext
{
    public function __construct(private readonly string $benchmarkPath, private readonly string $benchmarkClass)
    {
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
