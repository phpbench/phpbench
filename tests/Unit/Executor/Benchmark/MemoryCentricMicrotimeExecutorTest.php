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

use PhpBench\Executor\Benchmark\MemoryCentricMicrotimeExecutor;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Remote\Launcher;
use RuntimeException;

class MemoryCentricMicrotimeExecutorTest extends AbstractExecutorTestCase
{
    public function testRepressOutput(): void
    {
        $this->expectExceptionMessage('Benchmark made some noise');
        $this->expectException(RuntimeException::class);

        $this->executor->execute(
            $this->buildContext([
                'methodName' => 'benchOutput',
            ]),
            $this->resolveConfig([])
        );
    }

    protected function createExecutor(): BenchmarkExecutorInterface
    {
        return new MemoryCentricMicrotimeExecutor(new Launcher(null));
    }

    protected function assertExecute(ExecutionResults $results): void
    {
        self::assertCount(2, $results);

        $this->assertFileDoesNotExist($this->workspacePath('before_method.tmp'));
        $this->assertFileDoesNotExist($this->workspacePath('after_method.tmp'));
        $this->assertFileExists($this->workspacePath('revs.tmp'));

        // 10 revolutions + 1 warmup
        $this->assertStringEqualsFile($this->workspacePath('revs.tmp'), '11');
    }
}
