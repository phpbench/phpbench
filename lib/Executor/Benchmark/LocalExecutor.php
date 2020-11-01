<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function memory_get_peak_usage;

class LocalExecutor implements BenchmarkExecutorInterface
{
    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
    }

    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config): ExecutionResults
    {
        $className = $subjectMetadata->getBenchmark()->getClass();
        $benchmark = new $className;
        $methodName = $subjectMetadata->getName();
        $parameters = $iteration->getVariant()->getParameterSet()->getArrayCopy();

        foreach ($subjectMetadata->getBeforeMethods() as $afterMethod) {
            $benchmark->$afterMethod($parameters);
        }

        for($i = 0; $i < $iteration->getVariant()->getWarmup() ?: 0; $i++) {
            $benchmark->$methodName($parameters);
        }

        $start = microtime(true);

        for ($i = 0; $i < $iteration->getVariant()->getRevolutions(); $i++) {
            $benchmark->$methodName($parameters);
        }

        $end = microtime(true);

        foreach ($subjectMetadata->getAfterMethods() as $afterMethod) {
            $benchmark->$afterMethod($parameters);
        }

        return ExecutionResults::fromResults(
            new TimeResult((int)(($end - $start) * 1E6))
        );
    }
}
