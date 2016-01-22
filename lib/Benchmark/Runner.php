<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

use PhpBench\Environment\Supplier;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\PhpBench;
use PhpBench\Progress\Logger\NullLogger;
use PhpBench\Progress\LoggerInterface;
use PhpBench\Registry\Config;
use PhpBench\Registry\Registry;

/**
 * The benchmark runner.
 */
class Runner
{
    private $logger;
    private $benchmarkFinder;
    private $configPath;
    private $retryThreshold = null;
    private $executorRegistry;
    private $envSupplier;

    /**
     * @param BenchmarkFinder $benchmarkFinder
     * @param SubjectBuilder $subjectBuilder
     * @param string $configPath
     */
    public function __construct(
        BenchmarkFinder $benchmarkFinder,
        Registry $executorRegistry,
        Supplier $envSupplier,
        $retryThreshold,
        $configPath
    ) {
        $this->logger = new NullLogger();
        $this->benchmarkFinder = $benchmarkFinder;
        $this->executorRegistry = $executorRegistry;
        $this->envSupplier = $envSupplier;
        $this->configPath = $configPath;
        $this->retryThreshold = $retryThreshold;
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
    public function run(RunnerContext $context)
    {
        $executorConfig = $this->executorRegistry->getConfig($context->getExecutor());
        $executor = $this->executorRegistry->getService($executorConfig['executor']);

        // build the collection of benchmarks to be executed.
        $benchmarks = $this->benchmarkFinder->findBenchmarks($context->getPath(), $context->getFilters(), $context->getGroups());
        $suite = new Suite(
            $benchmarks,
            $context->getContextName(),
            new \DateTime(),
            $this->configPath,
            $context->getRetryThreshold($this->retryThreshold),
            $this->envSupplier->getInformations()
        );

        // log the start of the suite run.
        $this->logger->startSuite($suite);

        /* @var Benchmark */
        foreach ($suite->getBenchmarks() as $benchmark) {
            $this->logger->benchmarkStart($benchmark);
            $this->runBenchmark($executor, $context, $benchmark);
            $this->logger->benchmarkEnd($benchmark);
        }

        $this->logger->endSuite($suite);

        return $suite;
    }

    private function runBenchmark(
        ExecutorInterface $executor,
        RunnerContext $context,
        Benchmark $benchmark
    ) {
        if ($benchmark->getBeforeClassMethods()) {
            $executor->executeMethods($benchmark, $benchmark->getBeforeClassMethods());
        }

        foreach ($benchmark->getSubjects() as $subject) {
            if (true === $subject->getSkip()) {
                continue;
            }

            $this->logger->subjectStart($subject);
            $this->runSubject($executor, $context, $subject);
            $this->logger->subjectEnd($subject);
        }

        if ($benchmark->getAfterClassMethods()) {
            $executor->executeMethods($benchmark, $benchmark->getAfterClassMethods());
        }
    }

    private function runSubject(ExecutorInterface $executor, RunnerContext $context, Subject $subject)
    {
        $parameterSets = $context->getParameterSets($subject->getParameterSets());

        $paramsIterator = new CartesianParameterIterator($parameterSets);

        // override parameters
        $subject->setIterations($context->getIterations($subject->getIterations()));
        $subject->setRevs($context->getRevolutions($subject->getRevs()));
        $subject->setWarmup($context->getWarmup($subject->getWarmUp()));
        $subject->setSleep($context->getSleep($subject->getSleep()));

        foreach ($paramsIterator as $parameterSet) {
            $this->runIterations($executor, $context, $subject, $parameterSet);
        }
    }

    private function runIterations(ExecutorInterface $executor, RunnerContext $context, Subject $subject, ParameterSet $parameterSet)
    {
        $executorConfig = $this->executorRegistry->getConfig($context->getExecutor());

        // TODO: Move construction of variants to Factory?
        $variant = $subject->createVariant(
            $parameterSet,
            $subject->getIterations(),
            $subject->getRevs(),
            $subject->getWarmup(),
            $context->getRetryThreshold($this->retryThreshold)
        );

        $this->logger->variantStart($variant);

        try {
            foreach ($variant as $iteration) {
                $this->runIteration($executor, $executorConfig, $iteration, $subject->getSleep());
            }
        } catch (\Exception $e) {
            $variant->setException($e);
            $this->logger->variantEnd($variant);

            return;
        }

        $variant->computeStats();
        $this->logger->variantEnd($variant);

        while ($variant->getRejectCount() > 0) {
            $this->logger->retryStart($variant->getRejectCount());
            $this->logger->variantStart($variant);
            foreach ($variant->getRejects() as $reject) {
                $reject->incrementRejectionCount();
                $this->runIteration($executor, $executorConfig, $reject, $context->getSleep($subject->getSleep()));
            }
            $variant->computeStats();
            $this->logger->variantEnd($variant);
        }
    }

    public function runIteration(ExecutorInterface $executor, Config $executorConfig, Iteration $iteration, $sleep)
    {
        $this->logger->iterationStart($iteration);
        $result = $executor->execute($iteration, $executorConfig);

        if ($sleep) {
            usleep($sleep);
        }

        $iteration->setResult($result);
        $this->logger->iterationEnd($iteration);
    }
}
