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

use DTL\Invoke\Invoke;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Executor\Benchmark\MicrotimeExecutor;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Model\Benchmark;
use PhpBench\Model\ParameterSet;
use PhpBench\Registry\Config;
use PhpBench\Tests\PhpBenchTestCase;
use RuntimeException;

class MicrotimeExecutorTest extends PhpBenchTestCase
{
    /**
     * @var MicrotimeExecutor
     */
    private $executor;

    protected function setUp(): void
    {
        $this->initWorkspace();

        $launcher = new Launcher(null, null);
        $this->executor = new MicrotimeExecutor($launcher);
    }

    /**
     * It should create a script which benchmarks the code and returns
     * the time taken and the memory used.
     */
    public function testExecute(): void
    {
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
        self::assertCount(2, $results);

        $this->assertFileDoesNotExist($this->workspacePath('before_method.tmp'));
        $this->assertFileDoesNotExist($this->workspacePath('after_method.tmp'));
        $this->assertFileExists($this->workspacePath('revs.tmp'));

        // 10 revolutions + 1 warmup
        $this->assertStringEqualsFile($this->workspacePath('revs.tmp'), '11');
    }

    public function testRepressOutput(): void
    {
        $this->expectExceptionMessage('Benchmark made some noise');
        $this->expectException(RuntimeException::class);

        $this->executor->execute(
            $this->buildContext([
                'methodName' => 'benchOutput',
            ]),
            new Config('test', [])
        );
    }

    /**
     * It should execute methods before the benchmark subject.
     */
    public function testExecuteBefore(): void
    {
        $this->executor->execute(
            $this->buildContext([
                'methodName' => 'doSomething',
                'beforeMethods' => ['beforeMethod'],
            ]),
            new Config('test', [])
        );

        $this->assertFileExists($this->workspacePath('before_method.tmp'));
    }

    /**
     * It should execute methods after the benchmark subject.
     */
    public function testExecuteAfter(): void
    {
        $this->executor->execute(
            $this->buildContext([
                'methodName' => 'doSomething',
                'beforeMethods' => ['afterMethod'],
            ]),
            new Config('test', [])
        );

        $this->assertFileExists($this->workspacePath('after_method.tmp'));
    }

    /**
     * It should pass parameters to the benchmark method.
     */
    public function testParameters(): void
    {
        $this->executor->execute(
            $this->buildContext([
                'methodName' => 'parameterized',
                'parameters' => [
                    'one' => 'two',
                    'three' => 'four',
                ],
            ]),
            new Config('test', [])
        );

        $this->assertFileExists($this->workspacePath('param.tmp'));
        $params = json_decode(file_get_contents($this->workspacePath('param.tmp')), true);
        $this->assertEquals([
            'one' => 'two',
            'three' => 'four',
        ], $params);
    }

    /**
     * It should pass parameters to the before metadata and after metadata methods.
     */
    public function testParametersBeforeSubject(): void
    {
        $expected = new ParameterSet(0, [
            'one' => 'two',
            'three' => 'four',
        ]);

        $this->executor->execute(
            $this->buildContext([
                'methodName' => 'parameterized',
                'beforeMethods' => ['parameterizedBefore'],
                'afterMethods' => ['parameterizedAfter'],
                'parameters' => [
                    'one' => 'two',
                    'three' => 'four',
                ],
            ]),
            new Config('test', [])
        );


        $this->assertFileExists($this->workspacePath('parambefore.tmp'));
        $params = json_decode(file_get_contents($this->workspacePath('parambefore.tmp')), true);
        $this->assertEquals($expected->getArrayCopy(), $params);

        $this->assertFileExists($this->workspacePath('paramafter.tmp'));
        $params = json_decode(file_get_contents($this->workspacePath('paramafter.tmp')), true);
        $this->assertEquals($expected->getArrayCopy(), $params);
    }

    private function buildContext(array $config): ExecutionContext
    {
        return Invoke::new(ExecutionContext::class, $this->buildConfig($config));
    }

    private function buildConfig(array $config): array
    {
        return array_merge([
            'className' => 'PhpBench\Tests\Unit\Executor\benchmarks\MicrotimeExecutorBench',
            'classPath' => __DIR__ . '/../benchmarks/MicrotimeExecutorBench.php',
        ], $config);
    }
}
