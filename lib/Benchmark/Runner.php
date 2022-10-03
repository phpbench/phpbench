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

namespace PhpBench\Benchmark;

use PhpBench\Assertion\AssertionProcessor;
use PhpBench\Benchmark\Exception\RetryLimitReachedException;
use PhpBench\Benchmark\Exception\StopOnErrorException;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Environment\Supplier;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\HealthCheckInterface;
use PhpBench\Executor\MethodExecutorContext;
use PhpBench\Executor\MethodExecutorInterface;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSetsCollection;
use PhpBench\Model\ResolvedExecutor;
use PhpBench\Model\Result\RejectionCountResult;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\Progress\Logger\NullLogger;
use PhpBench\Progress\LoggerInterface;
use PhpBench\Registry\Config;
use PhpBench\Registry\ConfigurableRegistry;

/**
 * The benchmark runner.
 */
final class Runner
{
    public const DEFAULT_ASSERTER = 'comparator';

    /**
     * @var ConfigurableRegistry<BenchmarkExecutorInterface>
     */
    private $executorRegistry;

    /**
     * @var Supplier
     */
    private $envSupplier;

    /**
     * @var string
     */
    private $configPath;

    /**
     * @var AssertionProcessor
     */
    private $assertionProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigurableRegistry $executorRegistry,
        Supplier $envSupplier,
        AssertionProcessor $assertion,
        string $configPath = null
    ) {
        $this->logger = new NullLogger();
        $this->executorRegistry = $executorRegistry;
        $this->envSupplier = $envSupplier;
        $this->configPath = $configPath;
        $this->assertionProcessor = $assertion;
    }

    /**
     * Set the progress logger to use.
     *
     */
    public function setProgressLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Run all benchmarks (or all applicable benchmarks) in the given path.
     *
     * The $name argument will set the "name" attribute on the "suite" element.
     *
     * @param iterable<BenchmarkMetadata> $benchmarkMetadatas
     */
    public function run(iterable $benchmarkMetadatas, RunnerConfig $config): Suite
    {
        $suite = new Suite(
            $config->getTag(),
            new \DateTime(),
            $this->configPath
        );
        $suite->setEnvInformations($this->envSupplier->getInformations());

        // log the start of the suite run.
        $this->logger->startSuite($config, $suite);

        try {
            /* @var BenchmarkMetadata $benchmarkMetadata */
            foreach ($benchmarkMetadatas as $benchmarkMetadata) {
                $benchmark = $suite->createBenchmark($benchmarkMetadata->getClass());
                $this->runBenchmark($config, $benchmark, $benchmarkMetadata);
            }
        } catch (StopOnErrorException $e) {
        }

        $suite->generateUuid();

        $this->logger->endSuite($suite);

        return $suite;
    }

    private function runBenchmark(
        RunnerConfig $config,
        Benchmark $benchmark,
        BenchmarkMetadata $benchmarkMetadata
    ): void {
        // determine the executor
        $executorConfig = $this->executorRegistry->getConfig($config->getExecutor());
        $benchmarkExecutor = $this->executorRegistry->getService(
            $benchmarkMetadata->getExecutor() ? $benchmarkMetadata->getExecutor()->getName() : $executorConfig['executor']
        );
        $executor = $benchmarkExecutor;

        $this->executeBeforeMethods($benchmarkMetadata, $benchmarkExecutor);

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
            $subjectMetadata->setIterations($config->getIterations($subjectMetadata->getIterations() ?? [1]));
            $subjectMetadata->setRevs($config->getRevolutions($subjectMetadata->getRevs() ?? [1]));
            $subjectMetadata->setWarmup($config->getWarmup($subjectMetadata->getWarmup()));
            $subjectMetadata->setSleep($config->getSleep($subjectMetadata->getSleep()));
            $subjectMetadata->setRetryThreshold($config->getRetryThreshold($subjectMetadata->getRetryThreshold()));

            if ($config->getAssertions()) {
                $subjectMetadata->setAssertions($config->getAssertions());
            }

            if (null !== $config->getFormat()) {
                $subjectMetadata->setFormat($config->getFormat());
            }

            // resolve executor config for this subject
            $executorConfig = $this->executorRegistry->getConfig($config->getExecutor());

            if ($executorMetadata = $subjectMetadata->getExecutor()) {
                $executorConfig = $this->executorRegistry->getConfig($executorMetadata->getName());
            }
            $resolvedExecutor = ResolvedExecutor::fromNameAndConfig($executorConfig['executor'], $executorConfig);

            $benchmark->createSubjectFromMetadataAndExecutor($subjectMetadata, $resolvedExecutor);
        }

        $this->logger->benchmarkStart($benchmark);

        foreach ($benchmark->getSubjects() as $index => $subject) {
            $subjectMetadata = $subjectMetadatas[$index];

            $this->logger->subjectStart($subject);
            $executor = $this->executorRegistry->getService($subject->getExecutor()->getName());
            $this->runSubject($executor, $config, $subject, $subjectMetadata);
            $this->logger->subjectEnd($subject);
        }
        $this->logger->benchmarkEnd($benchmark);

        $this->executeAfterMethods($benchmarkMetadata, $benchmarkExecutor);
    }

    private function executeBeforeMethods(BenchmarkMetadata $benchmarkMetadata, BenchmarkExecutorInterface $executor): void
    {
        if (!$executor instanceof MethodExecutorInterface) {
            return;
        }

        if (!$benchmarkMetadata->getBeforeClassMethods()) {
            return;
        }

        $executor->executeMethods(
            MethodExecutorContext::fromBenchmarkMetadata($benchmarkMetadata),
            $benchmarkMetadata->getBeforeClassMethods()
        );
    }

    private function executeAfterMethods(BenchmarkMetadata $benchmarkMetadata, BenchmarkExecutorInterface $executor): void
    {
        if (!$executor instanceof MethodExecutorInterface) {
            return;
        }

        if (!$benchmarkMetadata->getAfterClassMethods()) {
            return;
        }

        $executor->executeMethods(
            MethodExecutorContext::fromBenchmarkMetadata($benchmarkMetadata),
            $benchmarkMetadata->getAfterClassMethods()
        );
    }

    private function runSubject(BenchmarkExecutorInterface $executor, RunnerConfig $config, Subject $subject, SubjectMetadata $subjectMetadata): Subject
    {
        if ($executor instanceof HealthCheckInterface) {
            $executor->healthCheck();
        }

        $collection = $subjectMetadata->getParameterSetsCollection();

        $parameterSets = (static function (array $configuredParameterSets, ParameterSetsCollection $subjectParameterSets) {
            if ($configuredParameterSets !== [[[]]]) {
                return ParameterSetsCollection::fromUnserializedParameterSetsCollection($configuredParameterSets);
            }

            return $subjectParameterSets;
        })($config->getParameterSets(), $subjectMetadata->getParameterSetsCollection());


        $paramsIterator = new CartesianParameterIterator($parameterSets);

        // create the variants.
        foreach ($paramsIterator as $parameterSet) {
            if (false === $parameterSet->nameMatches($config->getVariantFilters())) {
                continue;
            }

            foreach ($subjectMetadata->getIterations() as $nbIterations) {
                foreach ($subjectMetadata->getRevs() as $revolutions) {
                    foreach ($subjectMetadata->getWarmup() as $warmup) {
                        $variant = $subject->createVariant($parameterSet, $revolutions, $warmup);
                        $variant->spawnIterations($nbIterations);
                    }
                }
            }
        }

        // run the variants.
        $stopException = null;

        foreach ($subject->getVariants() as $variant) {
            if ($stopException) {
                $subject->remove($variant);

                continue;
            }

            try {
                $this->runVariant($executor, $subject->getExecutor()->getConfig(), $config, $subjectMetadata, $variant);
            } catch (StopOnErrorException $stopException) {
            }
        }

        if ($stopException) {
            throw $stopException;
        }

        return $subject;
    }

    private function runVariant(
        BenchmarkExecutorInterface $executor,
        Config $executorConfig,
        RunnerConfig $config,
        SubjectMetadata $subjectMetadata,
        Variant $variant
    ): void {
        $this->logger->variantStart($variant);
        $rejectCount = [];

        if ($baseline = $config->getBaselines()->findBaselineForVariant($variant)) {
            $variant->attachBaseline($baseline);
        }

        try {
            foreach ($variant->getIterations() as $iteration) {
                $rejectCount[spl_object_hash($iteration)] = 0;
                $this->runIteration($executor, $executorConfig, $iteration, $subjectMetadata);
            }
        } catch (\Exception $e) {
            $variant->setException($e);
            $this->logger->variantEnd($variant);

            if ($config->getStopOnError()) {
                throw new StopOnErrorException();
            }

            return;
        }

        $this->endVariant($subjectMetadata, $variant);

        while ($variant->getRejectCount() > 0) {
            $this->logger->retryStart($variant->getRejectCount());
            $this->logger->variantStart($variant);

            foreach ($variant->getRejects() as $reject) {
                $rejectCount[spl_object_hash($reject)]++;

                if ($subjectMetadata->getRetryLimit() && $rejectCount[spl_object_hash($reject)] > $subjectMetadata->getRetryLimit()) {
                    throw new RetryLimitReachedException(sprintf(
                        'Retry limit of %s exceeded',
                        $subjectMetadata->getRetryLimit()
                    ));
                }

                $this->runIteration($executor, $executorConfig, $reject, $subjectMetadata);
            }
            $this->endVariant($subjectMetadata, $variant);

            if (!isset($reject)) {
                continue;
            }

            $reject->setResult(new RejectionCountResult($rejectCount[spl_object_hash($reject)]));
        }
    }

    private function endVariant(SubjectMetadata $subjectMetadata, Variant $variant): void
    {
        $variant->computeStats();
        $variant->resetAssertionResults();

        foreach ($subjectMetadata->getAssertions() as $assertion) {
            $result = $this->assertionProcessor->assert($variant, $assertion);
            $variant->addAssertionResult($result);
        }

        $this->logger->variantEnd($variant);
    }

    public function runIteration(BenchmarkExecutorInterface $executor, Config $executorConfig, Iteration $iteration, SubjectMetadata $subjectMetadata): void
    {
        $this->logger->iterationStart($iteration);

        $results = $executor->execute(ExecutionContext::fromSubjectMetadataAndIteration($subjectMetadata, $iteration), $executorConfig);

        foreach ($results as $result) {
            $iteration->setResult($result);
        }

        $sleep = $subjectMetadata->getSleep();

        if ($sleep) {
            usleep($sleep);
        }

        $this->logger->iterationEnd($iteration);
    }
}
