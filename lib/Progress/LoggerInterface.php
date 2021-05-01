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

namespace PhpBench\Progress;

use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;

interface LoggerInterface
{
    /**
     * Log the end of a benchmark.
     *
     */
    public function benchmarkEnd(Benchmark $benchmark);

    /**
     * Log the start of a benchmark.
     *
     */
    public function benchmarkStart(Benchmark $benchmark);

    /**
     * Log the end of a benchmarking subject.
     *
     */
    public function subjectEnd(Subject $subject);

    /**
     * Log the end of a benchmarking subject.
     *
     */
    public function subjectStart(Subject $subject);

    /**
     * Log the end of an an iteration run.
     *
     * Errors should be checked using Variant->hasException()
     *
     */
    public function variantEnd(Variant $variant);

    /**
     * Log the start of an iteration run.
     *
     */
    public function variantStart(Variant $variant);

    /**
     * Log the end of an iteration.
     *
     */
    public function iterationEnd(Iteration $iteration);

    /**
     * Log the start of an iteration.
     *
     */
    public function iterationStart(Iteration $iteration);

    /**
     * Log the number of retries to be made.
     *
     */
    public function retryStart(int $rejectionCount);

    /**
     * Called at the start of the suite run.
     *
     */
    public function startSuite(RunnerConfig $config, Suite $suite);

    /**
     * Called at the end of the suite run.
     *
     */
    public function endSuite(Suite $suite);
}
