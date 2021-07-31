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

use PhpBench\Executor\Benchmark\RemoteExecutor;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Model\ParameterSet;
use PhpBench\Remote\Launcher;
use RuntimeException;

class RemoteExecutorTest extends AbstractExecutorTestCase
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

    public function testParameterClassDefinedOnlyInRemoteProcess(): void
    {
        $this->executor->execute(
            $this->buildContext([
                'methodName' => 'parameterized',
                'parameters' => ParameterSet::fromSerializedParameters('ad', [
                    'foo' => 'O:60:"PhpBench\Tests\Unit\Executor\benchmarks\ClassDefinedRemotely":1:{s:4:"test";s:5:"hello";}',
                ])
            ]),
            $this->resolveConfig([])
        );
        $params = json_decode($this->workspace()->getContents('param.tmp'), true);
        $foo = $params['foo'];
        self::assertArrayNotHasKey('__PHP_Incomplete_Class_Name', $foo);
        self::assertCount(1, $foo);
        self::assertEquals('hello', $foo['test']);
    }

    public function testUnsafeParameters(): void
    {
        $this->executor->execute(
            $this->buildContext([
                'methodName' => 'parameterized',
                'parameters' => ParameterSet::fromUnserializedValues('ad', [
                    'foo' => [
                        'bar'
                    ],
                ])
            ]),
            $this->resolveConfig([
                RemoteExecutor::OPTION_SAFE_PARAMETERS => false,
            ])
        );
        $params = json_decode($this->workspace()->getContents('param.tmp'), true);
        self::assertEquals(['bar'], $params['foo']);
    }

    protected function createExecutor(): BenchmarkExecutorInterface
    {
        return new RemoteExecutor(new Launcher(null));
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
