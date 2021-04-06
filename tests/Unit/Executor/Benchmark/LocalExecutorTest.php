<?php

namespace PhpBench\Tests\Unit\Executor\Benchmark;

use PhpBench\Executor\Benchmark\LocalExecutor;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Registry\Config;

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


    public function testWithBootstrap(): void
    {
        $executor = new LocalExecutor();
        $results = $this->executor->execute(
            $this->buildContext([
                'timeOut' => 0.0,
                'methodName' => 'doSomething',
                'parameterSetName' => 'one',
                'revolutions' => 10,
                'warmup' => 1,
            ]),
            new Config('test', [])
        );
    }
}
