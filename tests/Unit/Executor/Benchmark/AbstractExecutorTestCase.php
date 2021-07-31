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
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Model\Benchmark;
use PhpBench\Model\ParameterSet;
use PhpBench\Registry\Config;
use PhpBench\Tests\PhpBenchTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractExecutorTestCase extends PhpBenchTestCase
{
    /**
     * @var BenchmarkExecutorInterface
     */
    protected $executor;

    protected function setUp(): void
    {
        $this->initWorkspace();
        $this->executor = $this->createExecutor();
    }

    abstract protected function createExecutor(): BenchmarkExecutorInterface;

    abstract protected function assertExecute(ExecutionResults $reuslts): void;

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
            $this->resolveConfig([])
        );

        $this->assertExecute($results);
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
            $this->resolveConfig([])
        );

        $this->assertFileExists($this->workspace()->path('before_method.tmp'));
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
            $this->resolveConfig([])
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
                'warmup' => 1,
            ]),
            $this->resolveConfig([])
        );

        $this->assertFileExists($this->workspacePath('param.tmp'));
        $params = json_decode($this->workspace()->getContents('param.tmp'), true);
        $this->assertEquals([
            'one' => 'two',
            'three' => 'four',
        ], $params);
    }

    public function testParametersBeforeSubjectAndWarmup(): void
    {
        $expected = ParameterSet::fromUnserializedValues('0', [
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
            $this->resolveConfig([])
        );


        $this->assertFileExists($this->workspacePath('parambefore.tmp'));
        $params = json_decode((string)file_get_contents($this->workspace()->path('parambefore.tmp')), true);
        $this->assertEquals($expected->toUnserializedParameters(), $params);

        $this->assertFileExists($this->workspacePath('paramafter.tmp'));
        $params = json_decode((string)file_get_contents($this->workspace()->path('paramafter.tmp')), true);
        $this->assertEquals($expected->toUnserializedParameters(), $params);
    }

    /**
     * @param parameters $config
     */
    protected function buildContext(array $config): ExecutionContext
    {
        return Invoke::new(ExecutionContext::class, $this->buildConfig($config));
    }

    /**
     * @param parameters $config
     *
     * @return parameters
     */
    protected function buildConfig(array $config): array
    {
        $config = array_merge([
            'className' => 'PhpBench\Tests\Unit\Executor\benchmarks\MicrotimeExecutorBench',
            'classPath' => __DIR__ . '/../benchmarks/MicrotimeExecutorBench.php',
            'parameters' => [],
        ], $config);

        if (is_array($config['parameters'])) {
            $config['parameters'] = ParameterSet::fromUnserializedValues('test', $config['parameters'] ?? []);
        }

        return $config;
    }

    /**
     * @param parameters $config
     */
    protected function resolveConfig(array $config): Config
    {
        $resolver = new OptionsResolver();
        $this->createExecutor()->configure($resolver);

        return new Config('test', $resolver->resolve($config));
    }
}
