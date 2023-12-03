<?php

namespace PhpBench\Executor;

use PhpBench\Executor\HealthCheck\AlwaysFineHealthCheck;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompositeExecutor implements BenchmarkExecutorInterface, HealthCheckInterface, MethodExecutorInterface
{
    private readonly HealthCheckInterface $healthCheck;

    public function __construct(private readonly BenchmarkExecutorInterface $benchmarkExecutor, private readonly MethodExecutorInterface $methodExecutor, HealthCheckInterface $healthCheck = null)
    {
        $this->healthCheck = $healthCheck ?: new AlwaysFineHealthCheck();
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $this->benchmarkExecutor->configure($options);
    }

    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        return $this->benchmarkExecutor->execute($context, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function healthCheck(): void
    {
        $this->healthCheck->healthCheck();
    }

    /**
     * @param array<string> $methods
     */
    public function executeMethods(MethodExecutorContext $context, array $methods): void
    {
        $this->methodExecutor->executeMethods($context, $methods);
    }
}
