<?php

namespace PhpBench\Examples\Extension\ProgressLogger;

use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\Progress\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CatLogger implements LoggerInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function startSuite(RunnerConfig $config, Suite $suite)
    {
    }

    public function endSuite(Suite $suite)
    {
    }

    public function benchmarkStart(Benchmark $benchmark)
    {
    }

    public function benchmarkEnd(Benchmark $benchmark)
    {
    }

    public function subjectStart(Subject $subject)
    {
    }

    public function subjectEnd(Subject $subject)
    {
    }

    public function variantStart(Variant $variant)
    {
    }

    public function variantEnd(Variant $variant)
    {
    }

    public function iterationStart(Iteration $iteration)
    {
        $this->output->write('🐈');
    }

    public function iterationEnd(Iteration $iteration)
    {
    }

    public function retryStart(int $rejectionCount)
    {
    }
}
