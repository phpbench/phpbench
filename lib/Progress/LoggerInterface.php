<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Progress;

use PhpBench\Benchmark\Iteration;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Console\OutputAwareInterface;

interface LoggerInterface extends OutputAwareInterface
{
    /**
     * Log the end of a benchmark.
     *
     * @param BenchmarkMetadata $benchmark
     */
    public function benchmarkEnd(BenchmarkMetadata $benchmark);

    /**
     * Log the start of a benchmark.
     *
     * @param BenchmarkMetadata $benchmark
     */
    public function benchmarkStart(BenchmarkMetadata $benchmark);

    /**
     * Log the end of a benchmarking subject.
     *
     * @param SubjectMetadata $case
     */
    public function subjectEnd(SubjectMetadata $subject);

    /**
     * Log the end of a benchmarking subject.
     *
     * @param SubjectMetadata $case
     */
    public function subjectStart(SubjectMetadata $subject);

    /**
     * Log the end of an iteration.
     *
     * @param Iteration $iteration
     */
    public function iterationEnd(Iteration $iteration);

    /**
     * Log the start of an iteration.
     *
     * @param Iteration $iteration
     */
    public function iterationStart(Iteration $iteration);

    /**
     * Log the number of retries to be made.
     *
     * @param int $rejectionCount
     */
    public function retryStart($rejectionCount);
}
