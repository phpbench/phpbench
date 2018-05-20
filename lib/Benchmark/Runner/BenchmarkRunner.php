<?php

namespace PhpBench\Benchmark\Runner;

class BenchmarkRunner
{
    /**
     * @var ExecutorRegistry
     */
    private $executorRegistry;

    public function __construct(ExecutorRegistry $executorRegistry)
    {
        $this->executorRegistry = $executorRegistry;
    }

    public function runBenchmark(
        RunnerConfig $config,
        Benchmark $benchmark,
        BenchmarkMetadata $benchmarkMetadata
    ) {
        $executorConfig = $this->executorRegistry->getConfig($config->getExecutor());
        $executor = $this->executorRegistry->getService($benchmarkMetadata->getExecutor() ? $benchmarkMetadata->getExecutor()->getName() : $executorConfig['executor']);

        if ($benchmarkMetadata->getBeforeClassMethods()) {
            $executor->executeMethods($benchmarkMetadata, $benchmarkMetadata->getBeforeClassMethods());
        }

        $subjectMetadatas = array_filter($benchmarkMetadata->getSubjects(), function ($subjectMetadata) {
            if ($subjectMetadata->getSkip()) {
                return false;
            }

            return true;
        });

        // the keys are subject names, convert them to numerical indexes.
        $subjectMetadatas = array_values($subjectMetadatas);

        /** @var SubjectMetadata $subjectMetadata */
        foreach ($subjectMetadatas as $subjectMetadata) {

            // override parameters
            $subjectMetadata->setIterations($config->getIterations($subjectMetadata->getIterations()));
            $subjectMetadata->setRevs($config->getRevolutions($subjectMetadata->getRevs()));
            $subjectMetadata->setWarmup($config->getWarmup($subjectMetadata->getWarmup()));
            $subjectMetadata->setSleep($config->getSleep($subjectMetadata->getSleep()));
            $subjectMetadata->setRetryThreshold($config->getRetryThreshold($this->retryThreshold));

            if ($config->getAssertions()) {
                $subjectMetadata->setAssertions($this->assertionProcessor->assertionsFromRawCliConfig($config->getAssertions()));
            }

            $benchmark->createSubjectFromMetadata($subjectMetadata);
        }

        $this->logger->benchmarkStart($benchmark);
        foreach ($benchmark->getSubjects() as $index => $subject) {
            $subjectMetadata = $subjectMetadatas[$index];

            $this->logger->subjectStart($subject);
            $this->runSubject($executor, $config, $subject, $subjectMetadata);
            $this->logger->subjectEnd($subject);
        }
        $this->logger->benchmarkEnd($benchmark);

        if ($benchmarkMetadata->getAfterClassMethods()) {
            $executor->executeMethods($benchmarkMetadata, $benchmarkMetadata->getAfterClassMethods());
        }
    }

}
