<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Progress\Logger;

use PhpBench\Benchmark\Iteration;
use PhpBench\Benchmark\IterationCollection;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\SuiteDocument;
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
    public function benchmarkStart(BenchmarkMetadata $benchmark)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function benchmarkEnd(BenchmarkMetadata $benchmark)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function subjectStart(SubjectMetadata $subject)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function subjectEnd(SubjectMetadata $subject)
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
    public function iterationsStart(IterationCollection $iterations)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function iterationsEnd(IterationCollection $iterations)
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
    public function startSuite(SuiteDocument $suiteDocument)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function endSuite(SuiteDocument $suiteDocument)
    {
    }
}
