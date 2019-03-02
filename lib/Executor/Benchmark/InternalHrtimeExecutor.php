<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\HealthCheckInterface;
use PhpBench\Executor\MethodExecutorInterface;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InternalHrtimeExecutor implements BenchmarkExecutorInterface, HealthCheckInterface
{
    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options)
    {
    }

    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config): void
    {
        $className = $subjectMetadata->getBenchmark()->getClass();
        $subject = new $className;
        $name = $subjectMetadata->getName();

        $revs = $iteration->getVariant()->getRevolutions();
        $start= hrtime(true);

        for ($i = 0; $i < $revs; $i++) {
            $subject->$name();
        }

        $end = hrtime(true);

        $iteration->setResult(new TimeResult((
            $end - $start
        ) / 1000));
    }

    /**
     * {@inheritDoc}
     */
    public function healthCheck(): void
    {
        if (!function_exists('hrtime')) {
            throw new RuntimeException(
                'Function "hrtime" does not exist. hrtime requires at least PHP 7.3'
            );
        }
    }
}
