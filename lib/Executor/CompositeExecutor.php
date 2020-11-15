<?php

namespace PhpBench\Executor;

use PhpBench\Executor\HealthCheck\AlwaysFineHealthCheck;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompositeExecutor implements BenchmarkExecutorInterface, HealthCheckInterface, MethodExecutorInterface
{
    /**
     * @var BenchmarkExecutorInterface
     */
    private $benchmarkExecutor;

    /**
     * @var MethodExecutorInterface
     */
    private $methodExecutor;

    /**
     * @var HealthCheckInterface
     */
    private $healthCheck;

    public function __construct(BenchmarkExecutorInterface $benchmarkExecutor, MethodExecutorInterface $methodExecutor, HealthCheckInterface $healthCheck = null)
    {
        $this->benchmarkExecutor = $benchmarkExecutor;
        $this->methodExecutor = $methodExecutor;
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
