<?php

namespace PhpBench\Examples\Extension\ProgressLogger;

use PhpBench\Model\Benchmark;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\Model\Iteration;
use PhpBench\Progress\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CatLogger implements LoggerInterface
{
    /**
     * @var OutputInterface|null
     */
    private $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function variantStart(Variant $variant)
    {
    }

    public function startSuite(Suite $suite)
    {
    }

    public function retryStart(int $rejectionCount)
    {
    }

    public function iterationStart(Iteration $iteration)
    {
        $this->output->write('🐈');
    }

    public function iterationEnd(Iteration $iteration)
    {
    }

    public function subjectStart(Subject $subject)
    {
    }

    public function variantEnd(Variant $variant)
    {
    }


    public function subjectEnd(Subject $subject)
    {
    }

    public function benchmarkStart(Benchmark $benchmark)
    {
    }

    public function benchmarkEnd(Benchmark $benchmark)
    {
    }

    public function endSuite(Suite $suite)
    {
    }
}
