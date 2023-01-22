<?php

namespace PhpBench\Progress\Logger;

use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ResultInterface;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\Progress\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JsonLogger implements LoggerInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function benchmarkEnd(Benchmark $benchmark): void
    {
    }

    public function benchmarkStart(Benchmark $benchmark): void
    {
    }

    public function subjectEnd(Subject $subject): void
    {
    }

    public function subjectStart(Subject $subject): void
    {
    }

    public function variantEnd(Variant $variant): void
    {
    }

    public function variantStart(Variant $variant): void
    {
    }

    public function iterationEnd(Iteration $iteration): void
    {
        $variant = $iteration->getVariant();
        $data = array_merge([
                'benchmark' => $variant->getSubject()->getBenchmark()->getName(),
                'subject' => $variant->getSubject()->getName(),
                'variant' => $variant->getParameterSet()->getName(),
                'iteration' => $iteration->getIndex(),
            ], 
            array_reduce($iteration->getResults(), function (array $carry, ResultInterface $result) {
                return array_merge($carry, array_combine(
                    array_map(function (string $key) use ($result) {
                        return sprintf('%s_%s', $result->getKey(), $key);

                    }, array_keys($result->getMetrics())),
                    array_values($result->getMetrics())
                ));
            }, [])
        );
        $this->output->writeln(json_encode($data));
    }

    public function iterationStart(Iteration $iteration): void
    {
    }

    public function retryStart(int $rejectionCount): void
    {
    }

    public function startSuite(RunnerConfig $config, Suite $suite): void
    {
    }

    public function endSuite(Suite $suite): void
    {
    }
}
