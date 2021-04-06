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

    public function testRequiresFileIfItCantBeAutoloaded(): void
    {
        $this->workspace()->put('FooBench.php', '<?php class FoobarBench { public function benchFoo(): void {}}');

        $executor = new LocalExecutor();
        $executor->execute(
            $this->buildContext([
                'className' => 'FoobarBench',
                'classPath' => $this->workspace()->path('FooBench.php'),
                'methodName' => 'benchFoo',
            ]),
            new Config('test', [])
        );
        $this->addToAssertionCount(1);
    }

    public function testIncludesBootstrapOnce(): void
    {
        $this->workspace()->put('NewFooBench.php', '<?php class NewFoobarBench { public function benchFoo(): void { new Foobar(); }}');
        $this->workspace()->put('bootstrap.php', '<?php class Foobar {}');

        $executor = new LocalExecutor($this->workspace()->path('bootstrap.php'));
        $context = $this->buildContext([
            'className' => 'NewFoobarBench',
            'classPath' => $this->workspace()->path('NewFooBench.php'),
            'methodName' => 'benchFoo',
            'timeOut' => 0.0,
            'parameterSetName' => 'one',
            'revolutions' => 10,
            'warmup' => 1,
        ]);
        $executor->execute($context, new Config('test', []));
        $executor->execute($context, new Config('test', []));
        $this->addToAssertionCount(1);
    }
}
