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

namespace PhpBench\Benchmark\Executor;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Model\Iteration;
use PhpBench\Registry\Config;
use PhpBench\Registry\RegistrableInterface;

interface HealthCheckInterface
{
    /**
     * This method should throw an exception if the executor cannot be used in
     * the current environment.
     */
    public function healthCheck(): void;
}