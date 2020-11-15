<?php

namespace PhpBench\Tests\Unit\Executor\Benchmark;

use PhpBench\Executor\Benchmark\LocalExecutor;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionResults;

class LocalExecutorTest extends AbstractExecutorTestCase
{
    protected function createExecutor(): BenchmarkExecutorInterface
    {
        return new LocalExecutor();
    }

    protected function assertExecute(ExecutionResults $results): void
    {
        self::assertCount(1, $results);
    }
}
