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

use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\Progress\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NullLogger implements LoggerInterface
{
    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function benchmarkStart(Benchmark $benchmark)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function benchmarkEnd(Benchmark $benchmark)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function subjectStart(Subject $subject)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function subjectEnd(Subject $subject)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function iterationStart(Iteration $iteration)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function iterationEnd(Iteration $iteration)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function variantStart(Variant $variant)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function variantEnd(Variant $variant)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function retryStart($rejectionCount)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function startSuite(Suite $suite)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function endSuite(Suite $suite)
    {
    }
}
