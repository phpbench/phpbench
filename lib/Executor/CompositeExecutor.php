<?php

namespace PhpBench\Executor;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Executor\HealthCheck\AlwaysFineHealthCheck;
use PhpBench\Model\Iteration;
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

    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config): void
    {
        $this->benchmarkExecutor->execute($subjectMetadata, $iteration, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function healthCheck(): void
    {
        $this->healthCheck->healthCheck();
    }

    public function executeMethods(BenchmarkMetadata $benchmark, array $methods): void
    {
        $this->methodExecutor->executeMethods($benchmark, $methods);
    }
}
