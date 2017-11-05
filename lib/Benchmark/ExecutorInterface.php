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
interface ExecutorInterface extends RegistrableInterface
{
    /**
     * Execute the benchmark and return the result.
     *
     * NOTE: It is currently not, and probably never will be entirely necessary
     *       to pass the Iteration, as it contains no information other than a
     *       reference to the Variant that could be useful here. The Variant in
     *       its turn is only currently used to get the ParameterSet, but is
     *       likely more useful that the Iteration.
     */
    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config);

    /**
     * Execute arbitrary methods.
     *
     * This should be called based on the value of `@BeforeClassMethods` and `@AfterClassMethods`
     * and used to establish some persistent state.
     *
     * Methods called here cannot establish a runtime state.
     *
     * @param string[]
     */
    public function executeMethods(BenchmarkMetadata $benchmark, array $methods);

    public function healthCheck();
}
