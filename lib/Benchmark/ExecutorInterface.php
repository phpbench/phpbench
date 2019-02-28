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

namespace PhpBench\Benchmark;

use PhpBench\Benchmark\Executor\BenchmarkExecutorInterface;
use PhpBench\Benchmark\Executor\HealthCheckInterface;
use PhpBench\Benchmark\Executor\MethodExecutorInterface;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Model\Iteration;
use PhpBench\Registry\Config;
use PhpBench\Registry\RegistrableInterface;

/**
 * Executors are responsible for executing the benchmark class
 * and returning the timing metrics, and optionally the memory and profiling
 * data.
 */
interface ExecutorInterface extends BenchmarkExecutorInterface, HealthCheckInterface, MethodExecutorInterface
{
}
