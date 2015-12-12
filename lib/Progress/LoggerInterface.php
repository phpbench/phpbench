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
use PhpBench\Benchmark\IterationCollection;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\SuiteDocument;
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
     * Log the end of an an iteration run>.
     *
     * @param Iteration $iterations
     */
    public function iterationsEnd(IterationCollection $iterations);

    /**
     * Log the start of an iteration run.
     *
     * @param Iteration $iterations
     */
    public function iterationsStart(IterationCollection $iterations);

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

    /**
     * Called at the start of the suite run.
     *
     * @param SuiteDocument $suiteDocument
     */
    public function startSuite(SuiteDocument $suiteDocument);

    /**
     * Called at the end of the suite run.
     *
     * @param SuiteDocument $suiteDocument
     */
    public function endSuite(SuiteDocument $suiteDocument);
}
