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

namespace PhpBench\Progress\Logger;

use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\Progress\LoggerInterface;

class NullLogger implements LoggerInterface
{
    /**
     * {@inheritdoc}
     */
    public function benchmarkStart(Benchmark $benchmark): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function benchmarkEnd(Benchmark $benchmark): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function subjectStart(Subject $subject): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function subjectEnd(Subject $subject): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function iterationStart(Iteration $iteration): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function iterationEnd(Iteration $iteration): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function variantStart(Variant $variant): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function variantEnd(Variant $variant): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function retryStart(int $rejectionCount): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function startSuite(RunnerConfig $config, Suite $suite): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function endSuite(Suite $suite): void
    {
    }
}
