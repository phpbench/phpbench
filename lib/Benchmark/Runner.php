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

use PhpBench\Assertion\AssertionData;
use PhpBench\Assertion\AssertionFailure;
use PhpBench\Assertion\AssertionProcessor;
use PhpBench\Assertion\AssertionWarning;
use PhpBench\Benchmark\Exception\StopOnErrorException;
use PhpBench\Benchmark\Metadata\AssertionMetadata;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Environment\Supplier;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\RejectionCountResult;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\PhpBench;
use PhpBench\Progress\Logger\NullLogger;
use PhpBench\Progress\LoggerInterface;
use PhpBench\Registry\Config;
use PhpBench\Registry\ConfigurableRegistry;

/**
 * The benchmark runner.
 */
class Runner
{
    const DEFAULT_ASSERTER = 'comparator';

    /**
     * @var BenchmarkFinder
     */
    private $benchmarkFinder;

    /**
     * @var ConfigurableRegistry
     */
    private $executorRegistry;

    /**
     * @var Supplier
     */
    private $envSupplier;

    /**
     * @var float
     */
    private $retryThreshold;

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
        BenchmarkFinder $benchmarkFinder,
        ConfigurableRegistry $executorRegistry,
        Supplier $envSupplier,
        AssertionProcessor $assertion,
        float $retryThreshold = null,
        string $configPath = null
    ) {
        $this->logger = new NullLogger();
        $this->benchmarkFinder = $benchmarkFinder;
        $this->executorRegistry = $executorRegistry;
        $this->envSupplier = $envSupplier;
        $this->retryThreshold = $retryThreshold;
        $this->configPath = $configPath;
        $this->assertionProcessor = $assertion;
    }

    /**
     * Set the progress logger to use.
     *
     * @param LoggerInterface
     */
    public function setProgressLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Run all benchmarks (or all applicable benchmarks) in the given path.
     *
     * The $name argument will set the "name" attribute on the "suite" element.
     *
     * @param string $contextName
     * @param string $path
     */
    public function run($path, RunnerConfig $context)
    {
        $executorConfig = $this->executorRegistry->getConfig($context->getExecutor());
        $executor = $this->executorRegistry->getService($executorConfig['executor']);
        $executor->healthCheck();

        // build the collection of benchmarks to be executed.
        $benchmarkMetadatas = $this->benchmarkFinder->findBenchmarks($path, $context->getFilters(), $context->getGroups());
        $suite = new Suite(
            $context->getContextName(),
            new \DateTime(),
            $this->configPath
        );
        $suite->setEnvInformations((array) $this->envSupplier->getInformations());

        // log the start of the suite run.
        $this->logger->startSuite($suite);

        try {
            /* @var BenchmarkMetadata */
            foreach ($benchmarkMetadatas as $benchmarkMetadata) {
                $benchmark = $suite->createBenchmark($benchmarkMetadata->getClass());
                $this->runBenchmark($executor, $context, $benchmark, $benchmarkMetadata);
            }
        } catch (StopOnErrorException $e) {
        }

        $suite->generateUuid();

        $this->logger->endSuite($suite);

        return $suite;
    }

    private function runBenchmark(
        ExecutorInterface $executor,
        RunnerConfig $context,
        Benchmark $benchmark,
        BenchmarkMetadata $benchmarkMetadata
    ) {
        if ($benchmarkMetadata->getBeforeClassMethods()) {
            $executor->executeMethods($benchmarkMetadata, $benchmarkMetadata->getBeforeClassMethods());
        }

        // the keys are subject names, convert them to numerical indexes.
        $subjectMetadatas = array_filter($benchmarkMetadata->getSubjects(), function ($subjectMetadata) {
            if ($subjectMetadata->getSkip()) {
                return false;
            }

            return true;
        });
        $subjectMetadatas = array_values($subjectMetadatas);

        /** @var SubjectMetadata $subjectMetadata */
        foreach ($subjectMetadatas as $subjectMetadata) {

            // override parameters
            $subjectMetadata->setIterations($context->getIterations($subjectMetadata->getIterations()));
            $subjectMetadata->setRevs($context->getRevolutions($subjectMetadata->getRevs()));
            $subjectMetadata->setWarmup($context->getWarmup($subjectMetadata->getWarmUp()));
            $subjectMetadata->setSleep($context->getSleep($subjectMetadata->getSleep()));
            $subjectMetadata->setRetryThreshold($context->getRetryThreshold($this->retryThreshold));

            if ($context->getAssertions()) {
                $subjectMetadata->setAssertions($this->assertionProcessor->assertionsFromRawCliConfig($context->getAssertions()));
            }

            $benchmark->createSubjectFromMetadata($subjectMetadata);
        }

        $this->logger->benchmarkStart($benchmark);
        foreach ($benchmark->getSubjects() as $index => $subject) {
            $subjectMetadata = $subjectMetadatas[$index];

            $this->logger->subjectStart($subject);
            $this->runSubject($executor, $context, $subject, $subjectMetadata);
            $this->logger->subjectEnd($subject);
        }
        $this->logger->benchmarkEnd($benchmark);

        if ($benchmarkMetadata->getAfterClassMethods()) {
            $executor->executeMethods($benchmarkMetadata, $benchmarkMetadata->getAfterClassMethods());
        }
    }

    private function runSubject(ExecutorInterface $executor, RunnerConfig $context, Subject $subject, SubjectMetadata $subjectMetadata)
    {
        $parameterSets = $context->getParameterSets($subjectMetadata->getParameterSets());
        $paramsIterator = new CartesianParameterIterator($parameterSets);

        // create the variants.
        foreach ($paramsIterator as $parameterSet) {
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
        foreach ($subject->getVariants() as $variant) {
            $this->runVariant($executor, $context, $subjectMetadata, $variant);
        }

        return $subject;
    }

    private function runVariant(
        ExecutorInterface $executor,
        RunnerConfig $context,
        SubjectMetadata $subjectMetadata,
        Variant $variant
    ) {
        $executorConfig = $this->executorRegistry->getConfig($context->getExecutor());
        $this->logger->variantStart($variant);
        $rejectCount = [];

        try {
            foreach ($variant->getIterations() as $iteration) {
                $rejectCount[spl_object_hash($iteration)] = 0;
                $this->runIteration($executor, $executorConfig, $iteration, $subjectMetadata);
            }
        } catch (\Exception $e) {
            $variant->setException($e);
            $this->logger->variantEnd($variant);

            if ($context->getStopOnError()) {
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
                $this->runIteration($executor, $executorConfig, $reject, $subjectMetadata);
            }
            $this->endVariant($subjectMetadata, $variant);
            $reject->setResult(new RejectionCountResult($rejectCount[spl_object_hash($reject)]));
        }
    }

    private function endVariant(SubjectMetadata $subjectMetadata, Variant $variant)
    {
        $variant->computeStats();
        $variant->resetAssertionResults();

        /** @var AssertionMetadata $assertion */
        foreach ($subjectMetadata->getAssertions() as $assertion) {
            try {
                $this->assertionProcessor->assertWith(
                    self::DEFAULT_ASSERTER,
                    $assertion->getConfig(),
                    AssertionData::fromDistribution($variant->getStats())
                );
            } catch (AssertionWarning $warning) {
                $variant->addWarning($warning);
            } catch (AssertionFailure $failure) {
                $variant->addFailure($failure);
            }
        }

        $this->logger->variantEnd($variant);
    }

    public function runIteration(ExecutorInterface $executor, Config $executorConfig, Iteration $iteration, SubjectMetadata $subjectMetadata)
    {
        $this->logger->iterationStart($iteration);
        $executor->execute($subjectMetadata, $iteration, $executorConfig);

        $sleep = $subjectMetadata->getSleep();
        if ($sleep) {
            usleep($sleep);
        }

        $this->logger->iterationEnd($iteration);
    }
}
