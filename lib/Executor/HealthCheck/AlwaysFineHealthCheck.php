<?php

namespace PhpBench\Executor\HealthCheck;

use PhpBench\Executor\HealthCheckInterface;

class AlwaysFineHealthCheck implements HealthCheckInterface
{
    /**
     * {@inheritDoc}
     */
    public function healthCheck(): void
    {
    }
}
