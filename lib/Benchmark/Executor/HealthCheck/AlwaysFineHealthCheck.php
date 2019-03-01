<?php

namespace PhpBench\Benchmark\Executor\HealthCheck;

use PhpBench\Benchmark\Executor\HealthCheckInterface;

class AlwaysFineHealthCheck implements HealthCheckInterface
{
    /**
     * {@inheritDoc}
     */
    public function healthCheck(): void
    {
    }
}
