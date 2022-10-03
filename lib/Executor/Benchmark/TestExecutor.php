<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Executor\HealthCheckInterface;
use PhpBench\Executor\MethodExecutorContext;
use PhpBench\Executor\MethodExecutorInterface;
use PhpBench\Registry\Config;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestExecutor implements BenchmarkExecutorInterface, MethodExecutorInterface, HealthCheckInterface
{
    /**
     * @var array
     */
    private $executedMethods = [];

    /**
     * @var bool
     */
    private $healthChecked = false;

    /**
     * @var array<ExecutionContext>
     */
    private $executedContexts = [];

    /**
     * @var ExecutionContext|null
     */
    private $lastContext;

    private $index = 0;

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            'results' => [],
            'exception' => null,
        ]);
    }

    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        if ($config['exception']) {
            throw $config['exception'];
        }
        $this->executedContexts[] = $context;
        $this->lastContext = $context;

        return ExecutionResults::fromResults($config['results'][$this->index++ % count($config['results'])]);
    }

    public function executeMethods(MethodExecutorContext $context, array $methods): void
    {
        $this->executedMethods = array_merge($this->executedMethods, $methods);
    }

    /**
     * {@inheritDoc}
     */
    public function healthCheck(): void
    {
        $this->healthChecked = true;
    }

    public function lastContextOrException(): ExecutionContext
    {
        if (null === $this->lastContext) {
            throw new RuntimeException(
                'No subject has been executed'
            );
        }

        return $this->lastContext;
    }

    public function hasMethodBeenExecuted(string $name): bool
    {
        return in_array($name, $this->executedMethods);
    }

    public function hasHealthBeenChecked(): bool
    {
        return $this->healthChecked;
    }

    public function getExecutedContextCount(): int
    {
        return count($this->executedContexts);
    }
}
