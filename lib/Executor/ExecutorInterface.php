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

namespace PhpBench;

use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\HealthCheckInterface;
use PhpBench\Executor\MethodExecutorInterface;

/**
 * Executors are responsible for executing the benchmark class
 * and returning the timing metrics, and optionally the memory and profiling
 * data.
 */
interface Executor extends BenchmarkExecutorInterface, HealthCheckInterface, MethodExecutorInterface
{
}
