<?php

namespace PhpBench\Benchmark\Executor\HealthCheck;

use PhpBench\Benchmark\Executor\HealthCheckInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlwaysFineHealthCheck implements HealthCheckInterface
{
    /**
     * {@inheritDoc}
     */
    public function healthCheck(): void
    {
    }
}
